$(function(){
    var small_money = 'gkf8jfcuba';
    var big_money   = 'gkf8j8cup8';
  
    $("#"+small_money).on("change",function(){
      var money = $(this).val();
      if(((typeof money=='string')&&money.constructor==String)){
        money = parseFloat(money.replace(/,|\s/g,''));
      }else{
        money = parseFloat(money.data('val'));
      }
  money = money;
      var big = DX(money);
      $("#"+big_money).val(big).trigger('change');
    });
  
    function DX(num) {
      var strOutput = "";
      var strUnit = '仟佰拾亿仟佰拾万仟佰拾元角分';
      num += "00";
      var intPos = num.indexOf('.');
      if (intPos >= 0)
          num = num.substring(0, intPos) + num.substr(intPos + 1, 2);
      strUnit = strUnit.substr(strUnit.length - num.length);
      for (var i = 0; i < num.length; i++)
          strOutput += '零壹贰叁肆伍陆柒捌玖'.substr(num.substr(i, 1), 1) + strUnit.substr(i, 1);
      return strOutput.replace(/零角零分$/, '整').replace(/零[仟佰拾]/g, '零').replace(/零{2,}/g, '零').replace(/零([亿|万])/g, '$1').replace(/零+元/, '元').replace(/亿零{0,3}万/, '亿').replace(/^元/, "零元");
    }
  });
  $(document).on('v:done',function(){
      var company_uuid = localStorage.company_uuid;
      var company_name = localStorage.company_name;
      if(company_uuid){
          var option = '<option value="'+company_uuid+'">'+company_name+'</option>';
          var options = $('#company_company option[value="'+company_uuid+'"]');
          if(options.length<1){
            $("#company_company").append(option);
          }
          $("#company_company").val(company_uuid).trigger('change');
          localStorage.setItem("company_uuid",'');
          localStorage.setItem("company_name",'');
      }
    });
  
  /* ugly hack  */
  
  /* end  */
  
  var fcommit = function(action){
  
      if($('#remarks').val()==''){
          $('#remarks').val('同意');
      }
  
      var action = action || 'commit';
  
      if(action =='delete'){
          $('[lay-verify]').removeAttr('data-verify').removeAttr('lay-verify');
      }
  
      if (!isOpenNextWindow(action)){//跳到下一个待审批的流程中
          $('form #flow_action').val(action);
          $("form #submit-btn").trigger("click");
      }
  }
  
  
  /* ugly hack  */
  $(function(){
  /* end  */
  
  
  
  
  
  
  
    $(function(){
      var click = 'gnzyjng01y';//日前选择
      var year_text = 'jiaoge_year';//年份
      var quarter = 'jiaoge_quarter';//季度
      var month = 'jiaoge_month';//月份
  
      $('#'+click).change(function(){
        var date_on=new Date($(this).val());
        var year = date_on.getFullYear();
        var currMonth= date_on.getMonth()+1;
        var currQuarter = Math.floor( ( currMonth % 3 == 0 ? ( currMonth / 3 ) : ( currMonth / 3 + 1 ) ) );
        $('#'+year_text).val(year);
        $('#'+month).val(currMonth);
        $('#'+quarter).val('Q'+currQuarter);
      });
    });