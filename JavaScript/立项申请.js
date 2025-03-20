$(function(){

	$('#wh_smxm').change(function(){
	  special_cc_set()
	})

  setTimeout(special_cc_set,500)

  function special_cc_set(){
  	var wh_smxm = $('form #wh_smxm').val();
		var special_cc = $('#special_cc').val();
		var eidarr = [];
		var istrue = false;
		var eida = 'h3kz5hnklp';
		if(wh_smxm == ''){
			return false;
		}
		if(special_cc && special_cc.length > 0){

			for (let i = 0; i < special_cc.length; i++) {
				if(wh_smxm == '否'){
				  if(special_cc[i] == eida){
				  	istrue == true;
				  }
					eidarr.push(special_cc[i]);
				}else if(wh_smxm == '是' && special_cc[i] != eida ){
					eidarr.push(special_cc[i]);
				}
			}
			if(wh_smxm == '否' && istrue == false){
				eidarr.push(eida);
			}

		}else{
			if(wh_smxm == '否'){
			  eidarr.push(eida);
			}
		}
		$('#special_cc').val(eidarr)
		$('#special_cc').trigger("change");
  }
})
$(function() {
  setInterval(function(){
    var score_1 = $('#market_tec_score').val();
    var score_2 = $('#team_score').val()
    var score_3 = $('#investment_score').val()
    if(score_1 && isNaN(score_1)){
      layer.msg("请注意：只能输入数字");
      $('#market_tec_score').val(0)
    }
    if(score_2 && isNaN(score_2)){
      layer.msg("请注意：只能输入数字");
      $('#team_score').val(0)
    }
    if(score_3 && isNaN(score_3)){
      layer.msg("请注意：只能输入数字");
      $('#investment_score').val(0)
    }

    if(score_1 && score_1>10){
      layer.msg("请注意：每项评分不能超过10分");
      $('#market_tec_score').val(0)
    }
    if(score_2 && score_2>10){
      layer.msg("请注意：每项评分不能超过10分");
      $('#team_score').val(0)
    }
    if(score_3 && score_3>10){
      layer.msg("请注意：每项评分不能超过10分");
      $('#investment_score').val(0)
    }
  },500)


});
$(function(){
  var android = isMobile.Android();
  var ios = isMobile.iOS();
  var ipad = isMobile.iPad();
  if (android || ios || ipad){
    // 如果是ipad，单独处理
    if(ipad){
      // 为了防止影响其他环节的表单，需要切换节点id
      $('li[data-step=h9zi99y3z6] .pepm-table td[colspan="1"]').css('width','100%')
      $('li[data-step=heptv4quru] .pepm-table td[colspan="1"]').css('width','100%')
      $('.navigation-tab').css('display','none')
      // 找出需要改变位置的内容，和改变的地方
      var content_positon = $('td[kid=comments]').parent('tr')
      var content = $('td[kid=comments]')
      var total_score_positon = $('td[kid=total_score]').parent('tr')
      var total_score = $('td[kid=total_score]')
      var avg_score_positon = $('td[kid=average_score]').parent('tr')
      var avg_score = $('td[kid=_score]')
      content.remove()
      total_score.remove()
      avg_score.remove()
      // 遍历添加到后面
      for(var i=0;i<content.length;i++) {
        content_positon.eq(i).after(content.eq(i))
        total_score_positon.eq(i).after(total_score.eq(i))
        avg_score_positon.eq(i).after(avg_score.eq(i))
      }
    }
    var elem1=$('td[kid=h8wbk7sjf0]').parent('tr')
    var elem2=$('td[kid=h8wbwgv720]').parent('tr')
    var elem3=$('td[kid=h8wc1efhmn]').parent('tr')
    var elem4=$('td[kid=h8wcd1pci3]').parent('tr')
    var elem5=$('td[kid=h8wcd17hzr]').parent('tr')
    var elem6=$('td[kid=h8wcd04fje]').parent('tr')
    elem1.remove()
    elem2.remove()
    elem3.remove()
    elem4.remove()
    elem5.remove()
    elem6.remove()

    var html1 = elem4.eq(1).html();
    var html2 = elem5.eq(1).html();
    var html3 = elem6.eq(1).html();

    for(var i=0;i<$('td[kid=h8wbvy9ad2]').length;i++) {
      $('td[kid=h8wbio3wxi]').parent('tr').eq(i).after(html2)
      $('td[kid=h8wbvy9ad2]').parent('tr').eq(i).after(html2)
      $('td[kid=h8wbwi8qem]').parent('tr').eq(i).after(html2)
      $('td[kid=h8wbvy9ad2]').parent('tr').eq(i).before(html1)
      $('td[kid=h8wbvy9ad2]').parent('tr').eq(i).before(elem1.eq(i))
      $('td[kid=h8wbwi8qem]').parent('tr').eq(i).before(html1)
      $('td[kid=h8wbwi8qem]').parent('tr').eq(i).before(elem2.eq(i))
      $('td[kid=h8wc1d597i]').parent('tr').eq(i).after(elem3.eq(i))
      $('td[kid=h8wc1d597i]').parent('tr').eq(i).after(html1)
      $('td[kid=h8wbio3wxi]').parent('tr').eq(i).before(html3)
      $('td[kid=h8wbvy9ad2]').parent('tr').eq(i).before(html3)
      $('td[kid=h8wbwi8qem]').parent('tr').eq(i).before(html3)
    }

  }
})

$(document).ajaxSuccess(function(){
  var tr_count=$('[data-kid="hegidos8gl"] .tr_count').html();
  $('[data-kid="hegidos8gl"] .tr_count').remove();
  $('[data-kid="hegidos8gl"] tbody').append(tr_count);
  $('[data-kid="hegidos8gl"] tbody th').eq(0).text('各项平均分');
  var res = $('[data-kid="hegidos8gl"] tbody th').eq(-2).find('.list-number-widget__val').text()
  var avg = (res/3).toFixed(2)
  $('#').val(avg).trigger('change')
  change_table_style();
  change_table();
})
change_table_style();
change_table();

function change_table() {
  var market_tec_score = 0;
  var team_score = 0;
  var investment_score = 0;
  var total_score = 0;
  var _score = 0;
  var num = 0;
  $("[data-baseurl='pelxjydfbd'] tbody tr").each(function() {
    if($(this).find('.qiquan').text()!='弃权') {
      $("[data-baseurl='pelxjydfbd'] tbody th").eq(0).before($(this))
      num+=1;
    }
    market_tec_score += parseFloat($(this).find("[data-key='market_tec_score']").attr('data-val'))
    team_score += parseFloat($(this).find("[data-key='team_score']").attr('data-val'))
    investment_score += parseFloat($(this).find("[data-key='investment_score']").attr('data-val'))
    total_score += parseFloat($(this).find("[data-key='total_score']").attr('data-val'))
    _score += parseFloat($(this).find("[data-key='_score']").attr('data-val'))
  });
  if(num == 0) {
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(0).text(0);
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(1).text(0);
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(2).text(0);
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(3).text(0);
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(4).text(0);
  }else {
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(0).text((market_tec_score/num).toFixed(2));
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(1).text((team_score/num).toFixed(2));
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(2).text((investment_score/num).toFixed(2));
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(3).text((total_score/num).toFixed(2));
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(4).text((_score/num).toFixed(2));
  }
}

$(function() {

  $('.flow_item:last #market_tec_score').on('change', avg);
  $('.flow_item:last #team_score').on('change', avg);
  $('.flow_item:last #investment_score').on('change', avg);
  $('.flow_item:last #total_score').on('change', is_nan);


  function is_nan() {
    var total_score = $('.flow_item:last #total_score').val();
    if(window.isNaN(total_score)) {
      avg();
    }
  }

  function avg() {
    var market_tec_score = parseFloat($('.flow_item:last #market_tec_score').val());
    var team_score = parseFloat($('.flow_item:last #team_score').val());
    var investment_score = parseFloat($('.flow_item:last #investment_score').val());
    if(window.isNaN(market_tec_score)) {
      market_tec_score = 0;
    }
    if(window.isNaN(team_score)) {
      team_score = 0;
    }
    if(window.isNaN(investment_score)) {
      investment_score = 0;
    }
    var avg = ((market_tec_score+team_score+investment_score)/3).toFixed(2);
    var total_score = market_tec_score+team_score+investment_score;
    $('.flow_item:last #average_score').val(avg);
    $('.flow_item:last #total_score').val(total_score);
  }
})
function change_table_style() {
  $("[data-baseurl='pelxjydfbd'] tbody tr").each(function() {
    if($(this).find("[data-key='average_score']").attr('data-val') == "0" && $(this).find("[data-key='investment_score'] .list-number-widget .qiquan").length<1) {
      $(this).find("[data-key='market_tec_score'] .list-number-widget__val").text("");
      $(this).find("[data-key='market_tec_score'] .list-number-widget__label").text("");
      $(this).find("[data-key='market_tec_score']").parents('td').css("border-right",'none');

      $(this).find("[data-key='team_score'] .list-number-widget__val").text("");
      $(this).find("[data-key='team_score'] .list-number-widget__label").text("");
      $(this).find("[data-key='team_score']").parents('td').css("border-left",'none');
      $(this).find("[data-key='team_score']").parents('td').css("border-right",'none');

      $(this).find("[data-key='investment_score'] .list-number-widget__val").css("display",'none');
      $(this).find("[data-key='investment_score'] .list-number-widget__label").css("display",'none');
      $(this).find("[data-key='investment_score'] .list-number-widget").append("<span class='qiquan' style='text-align:center;display:block'>弃权</span>")
      $(this).find("[data-key='investment_score']").parents('td').css("border-left",'none');
      $(this).find("[data-key='investment_score']").parents('td').css("border-right",'none');

      $(this).find("[data-key='total_score'] .list-number-widget__val").text("");
      $(this).find("[data-key='total_score'] .list-number-widget__label").text("");
      $(this).find("[data-key='total_score']").parents('td').css("border-left",'none');
      $(this).find("[data-key='total_score']").parents('td').css("border-right",'none');

      $(this).find("[data-key='average_score'] .list-number-widget__val").text("");
      $(this).find("[data-key='average_score'] .list-number-widget__label").text("");
      $(this).find("[data-key='average_score']").parents('td').css("border-left",'none');
    }

  });
}

if($('.flow ul li:last').attr('data-step')=='heptv4quru') {
  $.post('?/service/vendor_bridge/'+websid+'/siic/update_msg', {eid:seg(3),next_step:'heptv4quru',}, function (res) {
  })
}


/*已审批环节隐藏*/
$(function () {
  var proc = debounce(function () {
    var $flow = $('.flow-body .formarea.flow');
    var $allFlowNodes = $('.flow-body .formarea.flow>ul>li')
    if (!$flow.length || !$allFlowNodes.length || $allFlowNodes.length == 1) {
      return;
    }

    var hasForm = false; // 正在审批
    var hasPending = false; // 等待审批
    $allFlowNodes.each(function () {
      var $this = $(this);
      if ($this.find('form').length) {
        hasForm = true;
      }
      if (!$this.attr('data-step')) {
        hasPending = true;
      }
    });

    if (!hasForm && !hasPending) {
      return;
    }

    // 追加折叠控件
    var $collapseHandler = $flow.find(".flow-collapse-handler");
    if (!$collapseHandler.length) {
      var $obj = $("<div class='flow-collapse-handler flex mb-4 p-4 bg-white text-xl bold cursor-pointer'><div class='b-arrow b-arrow--up inline-block mr-2 relative t-2'></div>已审批的节点</div>");
      $flow.find('ul').eq(0).prepend($obj);
      $collapseHandler = $flow.find(".flow-collapse-handler");
    }

    var toggleCollapse = function (e, show) {
      $collapseHandler.find('.b-arrow').toggleClass('b-arrow--down').toggleClass('b-arrow--up').toggleClass('t-1').toggleClass('t-2');
      $allFlowNodes.each(function () {
        var $this = $(this);
        if (!$this.attr('data-step') || $this.find('form.pepm-form').length) {
          return;
        }

        $this.toggle(show);
      });
    };

    $collapseHandler.on('click', toggleCollapse);
    toggleCollapse();
  }, 1);

  proc();
  $('.layout-pe_company-view').off('v:done', proc).on('v:done', proc);
});


    // 最晚打分时间自动增加三个工作日小时
$(function () {
  if($('.flow ul li:last').attr('data-step')=='h603jdlrvn') {
    $('.flow [data-step="h603jdlrvn"] #date_on').on("change",function() {
      var date_on = $('.flow [data-step="h603jdlrvn"] #date_on').val();
      var days = 1;
      var count = 0;
      var date_off = date_on;

      if(date_on == '' || date_on == undefined) {
        return;
      }
      
      while (count < 3 && days <= 50) {
        var add_date = addDate(date_on,days)
        count = daycount(date_on,add_date)-1;
        date_off = add_date;
        days++;
      }
      
      $('.flow [data-step="h603jdlrvn"] #date_off').val(date_off);
    })
  }

  function addDate(date,days) {
    var date = new Date(date);
    var hour = date.getHours(); 
    if(hour < 10) {
      hour = '0' + hour;
    }
    var minute = date.getMinutes();
    if(minute < 10) {
      minute = '0' + minute;
    }
    var time = date.setDate(date.getDate()+days);

    var date = new Date(time);
    var year = date.getFullYear();
    var month = date.getMonth()+1;
    if(month < 10) {
      month = '0' + month;
    }
    var day = date.getDate();
    if(day < 10) {
      day = '0' + day;
    }

    return year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
  }
})