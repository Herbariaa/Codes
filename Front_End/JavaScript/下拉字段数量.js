$(function(){
    $('#peixunduixiang').change(function(){
      var peixunduixiang = $('#peixunduixiang').val()
      if(typeof peixunduixiang === 'string') {
        peixunduixiang = peixunduixiang.split(',');
      }
      $("#peixunrenshu").val(peixunduixiang.length);
    })
  })





