function cal_gl(){
    console.log('cal_gl  2');
var muid=$("form.view-glsj2 input[name='uuid']").val();
var fid=$("td[fid='financial_import'] .file-list li").attr("fileid");
var filename=$("td[fid='financial_import'] .file-list li a")[0].innerText;
        $.ajax({
          type: 'POST',
          url:'?/service/vendor_bridge/'+websid+'/hhcap/cal_glsj2',
          data: {glsjid:muid,fd:fid,fn:filename},
          dataType: 'json',
          success: function(data){
            console.log('success2');
              if(typeof uid==typeof undefined)
                location.reload() ;
              else
                window.location.href="?/glsj2/view/about/"+muid+"/edit/";
            }
        });
}

var sInv=null;
var init_tablemm=function(){
var uid=$("form.view-glsj2 input[name='uuid']").val();
var fid=$("td[fid=financial_import] li").attr("fileid");

if(typeof fid==typeof  undefined||fid=="")return;
clearInterval(sInv);

        $.ajax({
          type: 'POST',
          url:'?/service/vendor_bridge/'+websid+'/hhcap/financial_import',
          data: {eid:uid,fid:fid},
          dataType: 'json',
          success: function(data){
          cal_gl();
            }
        });

};

var oldfid='';
$(function(){

var swautosave=$('form.ajax').attr('data-autosave');

var save_mute=function(){
if(swautosave){

   var $form=$('form.ajax');
   $form.find('.cache').val(2);

  var url = $form.attr('action');
  var data = $form.serialize();
  if(!submit_token){
    submit_token = $.cookie('submit_token');
  }

  if ($.cookie('submit_token')!=submit_token) {
    var notice = '<h1 style="font-size:28px;padding-top:20px;" class="open-duplicate-notice">请勿同时打开多个窗口编辑！</h1><p style="font-size:16px;margin-top:10px;margin-bottom:25px;">如需继续编辑，请关闭其他窗口后，刷新此页面。</p>';
    $('#canvas').html(notice);
    clearTimeout(save_target);
  }else if(cache_form($form,'check')){//打开新页面时检查页面中的表单数据是否做过修改
    $.post(url, data,function(){});
  }

}
}



$("td[fid=financial_import] .btn-upload-file").click(function(){
oldfid=$("td[fid='financial_import'] .file-list li").attr("fileid");
if(oldfid!=""||oldfid!=undefined){
    $("td[fid='financial_import'] .file-list li").remove();
}

save_mute();
if(sInv!=null)clearInterval(sInv);
 sInv= setInterval(init_tablemm,1000);
});

 $('.ajax-block').each(load_ajax_block);
})









   