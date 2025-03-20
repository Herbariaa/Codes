/*在（flow_sszbrcbxfksp、flow_shswyyjjrcbxfksp、flow_zhjjrcbxfksp）流程高级属性javascript中放入以下代码*/





/*将关联数据中新增的“金额”累加起来并填充到表单中的“合计金额”字段*/

var time = setInterval(function () {

  //节点uuid

  var step_key = $('input[name=step]').val();

  if (step_key != step_key) {

    clearInterval(time);

  }

  var money = 0;

  //循环查找的数据

  $.each($("form div[data-kid='fyqd'] table tbody tr"), function () {

    var type = $(this).find("td:eq(3) div.data-wrap").attr('data-val');

    //计算

    money += parseFloat($(this).find("td:eq(3)").find("div.data-wrap").attr('data-val').replace(/[,]/g, ""));

  });

  //赋值

  $("#sum").val(money);

}, 500)

/* end of将关联数据中新增的“金额”累加起来并填充到表单中的“合计金额”字段*/