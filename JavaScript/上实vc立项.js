$('li[data-step="huqkjeh5mp"] .flow_retract_btn,li[data-step="huqkjeh5mp"] .flow_retract_time').css('display','none')
$('li[data-step="haln4m6e9t"] .flow_retract_btn,li[data-step="haln4m6e9t"] .flow_retract_time').css('display','none')
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
    var eida = 'h7cvtlloxn';
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

//打分分数合规性判断与总分计算
$(function() {
  setInterval(function(){
    //负责人简易打分环节
    var score_1 = $("[data-step='huqkjeh5mp'] #market_tec_score").val();
    var score_2 = $("[data-step='huqkjeh5mp'] #team_score").val()
    var score_3 = $("[data-step='huqkjeh5mp'] #investment_score").val()
    var score_4 = $("[data-step='huqkjeh5mp'] #team_create_score").val()
    var score_5 = $("[data-step='huqkjeh5mp'] #valuation_rationality_score").val()
    var score_6 = $("[data-step='huqkjeh5mp'] #exit_determinacy_score").val()
    var weight1 = $("[data-step='huqkjeh5mp'] #weight1").val();
    var weight2 = $("[data-step='huqkjeh5mp'] #weight2").val();
    var weight3 = $("[data-step='huqkjeh5mp'] #weight3").val();
    var weight4 = $("[data-step='huqkjeh5mp'] #weight4").val();
    var weight5 = $("[data-step='huqkjeh5mp'] #weight5").val();
    var weight6 = $("[data-step='huqkjeh5mp'] #weight6").val();
    if(score_1 && isNaN(score_1)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='huqkjeh5mp'] #market_tec_score").val(0)
    }
    if(score_2 && isNaN(score_2)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='huqkjeh5mp'] #team_score").val(0)
    }
    if(score_3 && isNaN(score_3)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='huqkjeh5mp'] #investment_score").val(0)
    }
    if(score_4 && isNaN(score_4)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='huqkjeh5mp'] #team_create_score").val(0)
    }
    if(score_5 && isNaN(score_5)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='huqkjeh5mp'] #valuation_rationality_score").val(0)
    }
    if(score_6 && isNaN(score_6)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='huqkjeh5mp'] #exit_determinacy_score").val(0)
    }

    if(score_1 && score_1>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='huqkjeh5mp'] #market_tec_score").val(0)
    }
    if(score_2 && score_2>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='huqkjeh5mp'] #team_score").val(0)
    }
    if(score_3 && score_3>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='huqkjeh5mp'] #investment_score").val(0)
    }
    if(score_4 && score_4>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='huqkjeh5mp'] #team_create_score").val(0)
    }
    if(score_5 && score_5>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='huqkjeh5mp'] #valuation_rationality_score").val(0)
    }
    if(score_6 && score_6>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='huqkjeh5mp'] #exit_determinacy_score").val(0)
    }

    if(weight1 && isNaN(weight1)){
      $("[data-step='huqkjeh5mp'] #weight1").val(0)
    }
    if(weight2 && isNaN(weight2)){
      $("[data-step='huqkjeh5mp'] #weight2").val(0)
    }
    if(weight3 && isNaN(weight3)){
      $("[data-step='huqkjeh5mp'] #weight3").val(0)
    }
    if(weight4 && isNaN(weight4)){
      $("[data-step='huqkjeh5mp'] #weight4").val(0)
    }
    if(weight5 && isNaN(weight5)){
      $("[data-step='huqkjeh5mp'] #weight5").val(0)
    }
    if(weight6 && isNaN(weight6)){
      $("[data-step='huqkjeh5mp'] #weight6").val(0)
    }

    var total_score = (score_1*weight1+score_2*weight2+score_3*weight3+score_4*weight4+score_5*weight5+score_6*weight6)/100;
    if(total_score && isNaN(total_score)) {
      total_score = 0;
    }
    $("[data-step='huqkjeh5mp'] #total_score").val(total_score)

    //合伙人简易打分环节
    var score_1 = $("[data-step='haln4m6e9t'] #market_tec_score").val();
    var score_2 = $("[data-step='haln4m6e9t'] #team_score").val()
    var score_3 = $("[data-step='haln4m6e9t'] #investment_score").val()
    var score_4 = $("[data-step='haln4m6e9t'] #team_create_score").val()
    var score_5 = $("[data-step='haln4m6e9t'] #valuation_rationality_score").val()
    var score_6 = $("[data-step='haln4m6e9t'] #exit_determinacy_score").val()
    var weight1 = $("[data-step='haln4m6e9t'] #weight1").val();
    var weight2 = $("[data-step='haln4m6e9t'] #weight2").val();
    var weight3 = $("[data-step='haln4m6e9t'] #weight3").val();
    var weight4 = $("[data-step='haln4m6e9t'] #weight4").val();
    var weight5 = $("[data-step='haln4m6e9t'] #weight5").val();
    var weight6 = $("[data-step='haln4m6e9t'] #weight6").val();
    if(score_1 && isNaN(score_1)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='haln4m6e9t'] #market_tec_score").val(0)
    }
    if(score_2 && isNaN(score_2)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='haln4m6e9t'] #team_score").val(0)
    }
    if(score_3 && isNaN(score_3)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='haln4m6e9t'] #investment_score").val(0)
    }
    if(score_4 && isNaN(score_4)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='haln4m6e9t'] #team_create_score").val(0)
    }
    if(score_5 && isNaN(score_5)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='haln4m6e9t'] #valuation_rationality_score").val(0)
    }
    if(score_6 && isNaN(score_6)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='haln4m6e9t'] #exit_determinacy_score").val(0)
    }

    if(score_1 && score_1>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='haln4m6e9t'] #market_tec_score").val(0)
    }
    if(score_2 && score_2>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='haln4m6e9t'] #team_score").val(0)
    }
    if(score_3 && score_3>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='haln4m6e9t'] #investment_score").val(0)
    }
    if(score_4 && score_4>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='haln4m6e9t'] #team_create_score").val(0)
    }
    if(score_5 && score_5>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='haln4m6e9t'] #valuation_rationality_score").val(0)
    }
    if(score_6 && score_6>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='haln4m6e9t'] #exit_determinacy_score").val(0)
    }

    if(weight1 && isNaN(weight1)){
      $("[data-step='haln4m6e9t'] #weight1").val(0)
    }
    if(weight2 && isNaN(weight2)){
      $("[data-step='haln4m6e9t'] #weight2").val(0)
    }
    if(weight3 && isNaN(weight3)){
      $("[data-step='haln4m6e9t'] #weight3").val(0)
    }
    if(weight4 && isNaN(weight4)){
      $("[data-step='haln4m6e9t'] #weight4").val(0)
    }
    if(weight5 && isNaN(weight5)){
      $("[data-step='haln4m6e9t'] #weight5").val(0)
    }
    if(weight6 && isNaN(weight6)){
      $("[data-step='haln4m6e9t'] #weight6").val(0)
    }

    var total_score = (score_1*weight1+score_2*weight2+score_3*weight3+score_4*weight4+score_5*weight5+score_6*weight6)/100;
    if(total_score && isNaN(total_score)) {
      total_score = 0;
    }
    $("[data-step='haln4m6e9t'] #total_score").val(total_score)

    //上海生物医药基金总裁打分环节
    var score_1 = $("[data-step='hepv02hgvp'] #market_tec_score").val();
    var score_2 = $("[data-step='hepv02hgvp'] #team_score").val()
    var score_3 = $("[data-step='hepv02hgvp'] #investment_score").val()
    var score_4 = $("[data-step='hepv02hgvp'] #team_create_score").val()
    var score_5 = $("[data-step='hepv02hgvp'] #valuation_rationality_score").val()
    var score_6 = $("[data-step='hepv02hgvp'] #exit_determinacy_score").val()
    var weight1 = $("[data-step='hepv02hgvp'] #weight1").val();
    var weight2 = $("[data-step='hepv02hgvp'] #weight2").val();
    var weight3 = $("[data-step='hepv02hgvp'] #weight3").val();
    var weight4 = $("[data-step='hepv02hgvp'] #weight4").val();
    var weight5 = $("[data-step='hepv02hgvp'] #weight5").val();
    var weight6 = $("[data-step='hepv02hgvp'] #weight6").val();
    if(score_1 && isNaN(score_1)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='hepv02hgvp'] #market_tec_score").val(0)
    }
    if(score_2 && isNaN(score_2)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='hepv02hgvp'] #team_score").val(0)
    }
    if(score_3 && isNaN(score_3)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='h9zi99y3z6'] #investment_score").val(0)
    }
    if(score_4 && isNaN(score_4)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='hepv02hgvp'] #team_create_score").val(0)
    }
    if(score_5 && isNaN(score_5)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='hepv02hgvp'] #valuation_rationality_score").val(0)
    }
    if(score_6 && isNaN(score_6)){
      layer.msg("请注意：只能输入数字");
      $("[data-step='hepv02hgvp'] #exit_determinacy_score").val(0)
    }

    if(score_1 && score_1>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='hepv02hgvp'] #market_tec_score").val(0)
    }
    if(score_2 && score_2>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='hepv02hgvp'] #team_score").val(0)
    }
    if(score_3 && score_3>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='hepv02hgvp'] #investment_score").val(0)
    }
    if(score_4 && score_4>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='hepv02hgvp'] #team_create_score").val(0)
    }
    if(score_5 && score_5>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='hepv02hgvp'] #valuation_rationality_score").val(0)
    }
    if(score_6 && score_6>100){
      layer.msg("请注意：每项评分不能超过100分");
      $("[data-step='hepv02hgvp'] #exit_determinacy_score").val(0)
    }

    if(weight1 && isNaN(weight1)){
      $("[data-step='hepv02hgvp'] #weight1").val(0)
    }
    if(weight2 && isNaN(weight2)){
      $("[data-step='hepv02hgvp'] #weight2").val(0)
    }
    if(weight3 && isNaN(weight3)){
      $("[data-step='hepv02hgvp'] #weight3").val(0)
    }
    if(weight4 && isNaN(weight4)){
      $("[data-step='hepv02hgvp'] #weight4").val(0)
    }
    if(weight5 && isNaN(weight5)){
      $("[data-step='hepv02hgvp'] #weight5").val(0)
    }
    if(weight6 && isNaN(weight6)){
      $("[data-step='hepv02hgvp'] #weight6").val(0)
    }

    var total_score = (score_1*weight1+score_2*weight2+score_3*weight3+score_4*weight4+score_5*weight5+score_6*weight6)/100;
    if(total_score && isNaN(total_score)) {
      total_score = 0;
    }
    $("[data-step='hepv02hgvp'] #total_score").val(total_score)

    var market_tec_score = 0;
    var market_tec_score_num = 0;
    var team_score = 0;
    var team_score_num = 0;
    var investment_score = 0;
    var investment_score_num = 0;
    var team_create_score = 0;
    var team_create_score_num = 0;
    var valuation_rationality_score = 0;
    var valuation_rationality_score_num = 0;
    var exit_determinacy_score = 0;
    var exit_determinacy_score_num = 0;
    var total_score = 0;
    var total_score_num = 0;
    $("[data-step='hepv02hgvp'] [data-kid='hegidos8gl'] tbody tr").each(function() {
      if($(this).find("[data-key='total_score']").attr("data-val") == 0) {
        $(this).find("[data-itype='currency']").remove();
        $(this).find("[data-itype='select_new']").after("<td data-sort=\"0\" style=\"/* border-left: none; */text-align: center;\" coldata-itype=\"currency\" colspan=\"7\">弃权</td>")
      }

      if($(this).find("[data-key='market_tec_score']").attr("data-val") != undefined) {
        market_tec_score += parseFloat($(this).find("[data-key='market_tec_score']").attr("data-val"));
        market_tec_score_num++;
      }

      if($(this).find("[data-key='team_score']").attr("data-val") != undefined) {
        team_score += parseFloat($(this).find("[data-key='team_score']").attr("data-val"));
        team_score_num++;
      }

      if($(this).find("[data-key='investment_score']").attr("data-val") != undefined) {
        investment_score += parseFloat($(this).find("[data-key='investment_score']").attr("data-val"));
        investment_score_num++;
      }

      if($(this).find("[data-key='team_create_score']").attr("data-val") != undefined) {
        team_create_score += parseFloat($(this).find("[data-key='team_create_score']").attr("data-val"));
        team_create_score_num++;
      }

      if($(this).find("[data-key='valuation_rationality_score']").attr("data-val") != undefined) {
        valuation_rationality_score += parseFloat($(this).find("[data-key='valuation_rationality_score']").attr("data-val"));
        valuation_rationality_score_num++;
      }

      if($(this).find("[data-key='exit_determinacy_score']").attr("data-val") != undefined) {
        exit_determinacy_score += parseFloat($(this).find("[data-key='exit_determinacy_score']").attr("data-val"));
        exit_determinacy_score_num++;
      }

      if($(this).find("[data-key='total_score']").attr("data-val") != undefined) {
        total_score += parseFloat($(this).find("[data-key='total_score']").attr("data-val"));
        total_score_num++;
      }
    })

    var market_tec_score_avg = market_tec_score/market_tec_score_num;
    var team_score_avg = team_score/team_score_num;
    var investment_score_avg = investment_score/investment_score_num;
    var team_create_score_avg = team_create_score/team_create_score_num;
    var valuation_rationality_score_avg = valuation_rationality_score/valuation_rationality_score_num;
    var exit_determinacy_score_avg = exit_determinacy_score/exit_determinacy_score_num;
    var total_score_avg = total_score/total_score_num;

    if(isNaN(market_tec_score_avg)) {
      market_tec_score_avg = 0;
    }

    if(isNaN(team_score_avg)) {
      team_score_avg = 0;
    }

    if(isNaN(investment_score_avg)) {
      investment_score_avg = 0;
    }

    if(isNaN(team_create_score_avg)) {
      team_create_score_avg = 0;
    }

    if(isNaN(valuation_rationality_score_avg)) {
      valuation_rationality_score_avg = 0;
    }

    if(isNaN(exit_determinacy_score_avg)) {
      exit_determinacy_score_avg = 0;
    }

    if(isNaN(total_score_avg)) {
      total_score_avg = 0;
    }
    $("[data-step='hepv02hgvp'] [data-kid='hegidos8gl'] tfoot .tr_count th:first").text("各项平均分");
    $("[data-step='hepv02hgvp'] [data-kid='hegidos8gl'] tfoot .tr_count th").eq(1).find(".list-number-widget__val").text(market_tec_score_avg.toFixed(1));
    $("[data-step='hepv02hgvp'] [data-kid='hegidos8gl'] tfoot .tr_count th").eq(2).find(".list-number-widget__val").text(team_score_avg.toFixed(1));
    $("[data-step='hepv02hgvp'] [data-kid='hegidos8gl'] tfoot .tr_count th").eq(3).find(".list-number-widget__val").text(investment_score_avg.toFixed(1));
    $("[data-step='hepv02hgvp'] [data-kid='hegidos8gl'] tfoot .tr_count th").eq(4).find(".list-number-widget__val").text(team_create_score_avg.toFixed(1));
    $("[data-step='hepv02hgvp'] [data-kid='hegidos8gl'] tfoot .tr_count th").eq(5).find(".list-number-widget__val").text(valuation_rationality_score_avg.toFixed(1));
    $("[data-step='hepv02hgvp'] [data-kid='hegidos8gl'] tfoot .tr_count th").eq(6).find(".list-number-widget__val").text(exit_determinacy_score_avg.toFixed(1));
    $("[data-step='hepv02hgvp'] [data-kid='hegidos8gl'] tfoot .tr_count th").eq(7).find(".list-number-widget__val").text(total_score_avg.toFixed(1));

    var average = total_score_avg.toFixed(1);
    if(average && isNaN(average)){
      average = 0;
    }
    $("[data-step='h604rahaix'] #average").val(average)
  },500)


});
$(function(){
  var android = isMobile.Android();
  var ios = isMobile.iOS();
  var ipad = isMobile.iPad();
  if (android || ios || ipad){
    // 如果是ipad，单独处理
    /*
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
      var avg_score = $('td[kid=average_score]')
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
    /*
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
    */
  }
})
/*
$(document).ajaxSuccess(function(){
  var tr_count=$('[data-kid="hegidos8gl"] .tr_count').html();
  $('[data-kid="hegidos8gl"] .tr_count').remove();
  $('[data-kid="hegidos8gl"] tbody').append(tr_count);
  $('[data-kid="hegidos8gl"] tbody th').eq(0).text('各项平均分');
  var res = $('[data-kid="hegidos8gl"] tbody th').eq(-2).find('.list-number-widget__val').text()
  var avg = (res/3).toFixed(2)
  $('#average').val(avg).trigger('change')
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
  var average_score = 0;
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
    average_score += parseFloat($(this).find("[data-key='average_score']").attr('data-val'))
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
    $("[data-baseurl='pelxjydfbd'] tbody th .list-number-widget__val").eq(4).text((average_score/num).toFixed(2));
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

    var total_score = market_tec_score+team_score+investment_score;
    if(window.isNaN(total_score)) {
      total_score = 0;
    }
    var avg = (total_score/3).toFixed(2);
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
*/

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

// 最晚打分时间自动加三个工作日小时
$(function () {
  if($('.flow ul li:last').attr('data-step')=='h604rah9op') {
    $('.flow [data-step="h604rah9op"] #date_on').on("change",function() {
      var date_on = $('.flow [data-step="h604rah9op"] #date_on').val();
      var days = 1;
      var count = 0;
      var date_off = date_on;

      if(date_on == '' || date_on == undefined) {
        return;
      }

      while (count < 1 && days <= 50) {
        var add_date = addDate(date_on,days)
        count = daycount(date_on,add_date)-1;
        date_off = add_date;
        days++;
      }

      $('.flow [data-step="h604rah9op"] #date_off').val(date_off);
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