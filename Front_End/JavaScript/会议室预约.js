$(function(){
  $('.drawer__content').html('');
})
// 钱屹帆版本基础上修改，meetingroom_reserve js
var uuid = '',
  reserve_date_start = null, //会议开始时间
  reserve_date_end = null, //会议结束时间
  reserve_room = null, //会议地点
  reserve_duration = null, //会议持续时间
  reserve_system = null, //ZOOM
  reserve_repeat = null, //重复
  reserve_repeat_until = null; //直到

// 缓存前一次检查结果？
var defy_date_start,
  defy_date_end,
  defy_room,
  defy_duration,
  defy_system,
  defy_repeat,
  defy_repeat_until,
  para;

var room = false,
  system = false;


function get_all_datas() {
  //所有数据收集都放到这边来处理

  // 当前编辑uuid
  uuid = $("[name=uuid]").val();
  //获取会议室
  reserve_room = $("[name='data[ftwbux5ys7]']").val();
  //获取会议时间 开始
  var reserve_date_val = $("[name='data[ftwbux5z7m]']").val();
  console.log(reserve_date_val);
  reserve_date_start = reserve_date_val ? reserve_date_val.replace("T", " ") : '';
  //开始时间时间戳
  reserve_date_stamp = (new Date(reserve_date_start)).getTime()

  //获取会议时间 结束
  var reserve_date_val_end = $("[name='data[date_off]']").val();
  reserve_date_end = reserve_date_val_end ? reserve_date_val_end.replace("T", " ") : '';
  //结束时间时间戳
  reserve_date_end_stamp = (new Date(reserve_date_end)).getTime()

  //获取会议的持续时间 持续时间由开始时间和结束时间计算得出
  //时长 为 8:00-20:00 算为有效时长
  function cal_duration(){
    //开始时间的当天 8点
    var start_8 = reserve_date_start.split(' ')[0]+' 08:00';
    var start_8_s = (new Date(start_8)).getTime()
    //开始时间的当天 20点
    var start_20 = reserve_date_start.split(' ')[0]+' 20:00';
    var start_20_s = (new Date(start_20)).getTime()
    //结束时间的当天 8点
    var end_8 = reserve_date_end.split(' ')[0]+' 08:00';
    var end_8_s = (new Date(end_8)).getTime()
    //结束时间的当天 20点
    var end_20 = reserve_date_end.split(' ')[0]+' 20:00';
    var end_20_s = (new Date(end_20)).getTime()

    var hours = 0

    //判断间隔了几天
    var days =(end_8_s -  start_8_s)/1000 / 86400;
    //当天结束的
    switch(days){
      case 0:
        hours = (reserve_date_end_stamp - reserve_date_stamp)/1000 /60 /15 ;
        break;
      case 1:
        hours = (start_20_s - reserve_date_stamp + reserve_date_end_stamp - end_8_s)/1000 /60 /15 ;
        break;
      default:
        hours = (start_20_s - reserve_date_stamp + reserve_date_end_stamp - end_8_s)/1000 /60 /15 ;
        hours += (days-1)*48;
        break;
    }

    //时长 label
    $("[name='data[ftwbux5zd5]']").val(hours-1);
    $("[name='data[ftwbux5zd5_label]']").val(Math.floor(hours*15/60)+":"+String(Math.round((hours%4)*15)).padStart(2,"00"));

    return hours;
  }
  reserve_duration = cal_duration();
  //ZOOM 是否需要
  reserve_system = $('[kid="meeting_system"] input[type="checkbox"]').val();

  //获取是否重复
  reserve_repeat = $("[name='data[repeat]']").val();
  //获取日期直到时间
  reserve_repeat_until = $("[name='data[repeat_until]']").val();
}

//zoom系统时间查重
function checkConflict() {
  var $btn = $(".view-meetingroom_reserve [action=cache]").closest('.pepm-action-btns');

  get_all_datas();
  // 跟上一次检查数据相同，跳过  没必要缓存吧。。。
  if(defy_duration == reserve_duration && defy_date_start == reserve_date_start && defy_date_end == reserve_date_end && defy_room == reserve_room && defy_system==reserve_system && defy_system==reserve_repeat && defy_system==reserve_repeat_until){
    return;
  }
  var href = window.location.href;
  var index = href.indexOf('edit');
  if (index == -1) {
    $btn.hide();
  }

  // 注意：reserve_duration 值为 "0" 时为15分钟时长

  //if (_.isEmpty(reserve_date_start) || _.isEmpty(reserve_date_end) || _.isEmpty(reserve_room) || !reserve_duration || reserve_duration<0) {
  //console.log('3333333333333333')
  //console.log(reserve_date_start)
  //console.log(reserve_date_end)
  //console.log(reserve_room)
  //console.log(reserve_duration)
  //console.log(reserve_duration)
  //return;
  //}

  para = {
    uuid: uuid,
    date: reserve_date_start,
    
    date_end: reserve_date_end,
    duration: reserve_duration,
    room: reserve_room,
    system: reserve_system,
    repeat: reserve_repeat,
    repeat_until: reserve_repeat_until,
  };

  var url = "?/service/vendor_bridge/" + websid + "/siic/check_system";
  console.log(para);
  $.ajax({
    type: "POST",
    url: url,
    data: para,
    dataType: "json",
    success: function (data) {
      defy_date_start = reserve_date_start;
      defy_date_end = reserve_date_end;
      defy_duration = reserve_duration;
      defy_room = reserve_room;
      defy_system = reserve_system;
      defy_repeat = reserve_repeat;
      defy_repeat_until = reserve_repeat_until;
      if (data.ret) {
        layer.open({
          title: '提示',
          content: '您选的时间已经有人预订，请选择其他时间',
        });
        conflict = true;
      } else {
        conflict = false;
      }

      if (conflict) {
        $btn.hide(); //提交禁用按钮
      } else {
        $btn.show(); //提交按钮可用
      }
    },
    error: function (data) {
      console.log(data);
    },
  });
}

var autoCheck = _.debounce(function () {

  checkConflict();
}, 500);

//setTimeout(function(){
// ZOOM选择后触发？
//$("[kid=meeting_system] .layui-form-checkbox").off('click').on("click", function () {
//if (reserve_system == "否") defy_system = "否";
//autoCheck();
//});

//时长选择后触发
/*$("[name='data[ftwbux5zd5]']").on("change", function () {
autoCheck();
});*/



//}, 1000);

// 去掉暂存按钮
$(".view-meetingroom_reserve [action=cache]").hide();
//更新一下最新的数据
if($(".view-meetingroom_reserve .edit-btn").length == 0) {
  var timer_checkConflict = setInterval(function () {
    if($("[name='data[ftwbux5ys7]']").val() != null) {
      var ftwbux5zks = $('#ftwbux5zks').val()
      if(typeof ftwbux5zks === 'string') {
        ftwbux5zks = ftwbux5zks.split(',');
      }
      $("#hktj7ng7dt").val(ftwbux5zks.length);
      checkConflict();

      //日期选择后触发
      $("[name='data[ftwbux5z7m]'],[name='data[date_off]']").on("change", function () {

        if($(this).attr("id") == 'ftwbux5z7m') {
          if($(".view-meetingroom_reserve #ftwbux5z7m").val() != '') {
            // 添加一小时
            var newTime = new Date($(this).val());
            newTime.setHours(newTime.getHours() + 1);

            // 格式化新的时间
            //var date_off = newTime.toLocaleString().replace("00:00","00").replace("30:00","30").replaceAll("/","-");
            var date_off = formatDate(newTime, 'yyyy-MM-dd HH:mm');
            $(".view-meetingroom_reserve #date_off").val(date_off);
          }

        }

        if($(this).attr("id") == 'date_off') {
          if($(".view-meetingroom_reserve #ftwbux5z7m").val() == '' && $(".view-meetingroom_reserve #date_off").val() != '') {
            $(".view-meetingroom_reserve #date_off").val('');
            $(".view-meetingroom_reserve #ftwbux5z7m").val('');
            layer.msg("请先选择开始时间");
            return;
          }
        }

        if($(".view-meetingroom_reserve #ftwbux5z7m").val() == '' && $(".view-meetingroom_reserve #date_off").val() == '') {
          return;
        }

        if($(".view-meetingroom_reserve #date_off").val() < $(".view-meetingroom_reserve #ftwbux5z7m").val()) {
          // 添加一小时
          //var newTime = new Date($(this).val());
          //newTime.setHours(newTime.getHours() + 1);

          // 格式化新的时间
          //var date_off = newTime.toLocaleString().replace("00:00","00").replaceAll("/","-");
          $(".view-meetingroom_reserve #date_off").val('');
          $(".view-meetingroom_reserve #ftwbux5z7m").val('');
          layer.msg("结束时间应该大于开始时间");
          return;
        }
        autoCheck();

      });

      //会议室选择后触发，移动端bug导致id重复，不能用#ftwbux5ys7取dom
      $("[name='data[ftwbux5ys7]']").on("change", function () {
        autoCheck();
      });

      //重复选择后触发，移动端bug导致id重复，不能用#ftwbux5ys7取dom
      $("[name='data[repeat]'],[name='data[repeat_until]']").on("change", function () {
        autoCheck();
      });
      clearInterval(timer_checkConflict)
    }
  },100)
}

function formatDate(date, format) {
  const map = {
    'MM': date.getMonth() + 1, // 月份
    'dd': date.getDate(), // 日
    'yyyy': date.getFullYear(), // 年
    'HH': date.getHours(), // 小时
    'mm': date.getMinutes(), // 分钟
    //'ss': date.getSeconds(), // 秒
  };

  return format.replace(/MM|dd|yyyy|HH|mm|ss/g, match => {
    return map[match].toString().padStart(2, '0');
  });
}

$(function(){
  var time = new Date();
  year = time.getFullYear()
  month = time.getMonth()+1
  day = time.getDate()

  result = year+'-'+month+'-'+day+' '+'09'+':'+'00';
  result1 = year+'-'+month+'-'+day+' '+'18'+':'+'00';

  $('#canvas form[data-mod="meetingroom_reserve"]').find('#ftwbux5z7m').val(result)
  $('#canvas form[data-mod="meetingroom_reserve"]').find('#date_off').val(result1)
  $('#canvas form[data-mod="meetingroom_reserve"]').find('#ftwbux5zd5_label').val('9:00')

  $('#ftwbux5zks').change(function(){
    var ftwbux5zks = $('#ftwbux5zks').val()
    if(typeof ftwbux5zks === 'string') {
      ftwbux5zks = ftwbux5zks.split(',');
    }
    //$('#hktj7ng7dt').val($('#ftwbux5zks').val().split(',').length)
    $("#hktj7ng7dt").val(ftwbux5zks.length);
  })
})

$(".view-meetingroom_reserve #repeat_until").on("change",function() {
  if(($(".view-meetingroom_reserve #repeat").val() != '' || $(".view-meetingroom_reserve #repeat").val() != undefined) && $(".view-meetingroom_reserve #repeat").val() != '不重复') {
    if($(".view-meetingroom_reserve #repeat_until").val() <= $(".view-meetingroom_reserve #date_off").val()) {
      $(".view-meetingroom_reserve #repeat_until").val('');
      layer.msg("直到时间不能小于会议结束时间");
    }
  }

})

$(".view-meetingroom_reserve .meetingroom_reserve_submit").on("click",function() {
  var repeat = $(".view-meetingroom_reserve #repeat").val();
  var repeat_until = $(".view-meetingroom_reserve #repeat_until").val();
  if(repeat != '' && repeat != undefined && repeat != '不重复' && (repeat_until == '' || repeat_until == undefined)) {
    event.preventDefault();
    layer.msg("请填写直到时间在提交");
  }
})

function close_meetingroom_reserve() {
  if($('.meetingroom_reservation').length != 0) {
    var timer = setInterval(function () {
      if($(".view-meetingroom_reserve .meetingroom_reserve_submit").length == 0) {
        //window.location.href = "?/meetingroom/week";
        $(".this-week").click()
        clearInterval(timer);
      }
    },100)
  }
  if($('.meetingroom_reservation_day').length != 0) {
    var time = $("#today").text()
    var timer_day = setInterval(function () {
      if($(".view-meetingroom_reserve .meetingroom_reserve_submit").length == 0) {
        //window.location.href = "?/meetingroom/overview";
        $("#today").click()
        var date = new Date(time);
        var year = date.getFullYear();
        var month = date.getMonth() + 1; // 注意，月份是从 0 开始的，所以需要加 1
        var day = date.getDate();
        time = year+'-'+month+'-'+day;
        $("[lay-ymd="+"'"+time+"'"+"]").click()
        clearInterval(timer_day);
      }
    },100)
  }
}

$(".layui-layer-setwin").on("click",function(){
  close_meetingroom_reserve();

})
// 当点击非弹窗区域时触发该函数
$('html').on('click', function(event) {
  var target = event.target;
  // 判断点击目标元素不属于弹窗或者弹窗内容的子元素
  if ((!$('[type="modalbox"]').is(target) && $('[type="modalbox"] *').has(target).length === 0)) {
    if(!$(target).hasClass("layui-layer-modal-open")) {
      close_meetingroom_reserve();
      $('html').off('click');
      // 这里可以添加需要执行的代码
    }

  }
});