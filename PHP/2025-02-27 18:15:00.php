<style type="text/css">
  .str_body{
    color:#333;
  }
  .change_flex{
    display: flex;
    flex-direction: row;
    flex-wrap:wrap;
  }
  .change_width1{
    width: 100%;
  }
  .change_width2{
    width: 42%;
  }
  .change_width3{
    width: 45%;
  }
  .change_width4{
    width: 14.6%;
  }
  .change_flex .data_box{
    /*text-align: center;*/
  }
  .change_flex .title_top{
    margin-left: -12px;
    position: relative;
    padding: 0
  }
  .change_flex .title img{
    width: 142px;
    height: 46px;
  }
  .change_flex .title .title_left{
    position: absolute;
    top: 5px;
    color: #fff;
    left: 16px;
    line-height: 28px;
    font-size: 20px;
  }
  .change_flex .title .title_right{
    position: absolute;
    top: 5px;
    right: -6px;
    font-size: 14px;
    line-height: 30px;
    font-weight: 600;
  }
  .change_flex .title .title_right span{
    font-size: 24px;
  }
  .change_flex_column{
    display: flex;
    flex-direction: column;
  }
  .ztqk_box{
    background: <?=$changeimg['hvbf8195m3']?>
  }
  .ztqk_box .title_right{
    color: <?=$changeimg['hvbfdmqb00']?>
  }
  .zyjj_box{
    background: <?=$changeimg['hvbfe68pls']?>
  }
  .zyjj_box .title_right{
    color: <?=$changeimg['hvbfei59ly']?>
  }
  .mjj_box{
    background: <?=$changeimg['hvbfeskz4e']?>
  }
  .mjj_box .title_right{
    color: <?=$changeimg['hvbff1csf7']?>
  }
  .zgzjj_box{
    background: <?=$changeimg['hvbfi6yf4g']?>
  }
  .zgzjj_box .title_right{
    color: <?=$changeimg['hvbfigy2j2']?>
  }
  .ztjj_box{
    background: <?=$changeimg['hvbfiqcgvr']?>
  }
  .ztjj_box .title_right{
    color: <?=$changeimg['hvbfivkoa1']?>
  }
  .zyjj_box1 {
    background: #F5F0FF !important; /* 更浅的紫色背景 */
  }

  .zyjj_box1 .title_right {
    color: #800080 !important; /* 深紫色文本 */
  }

  .mjj_box1 {
    background: #FFFACD !important; /* 浅黄色背景 */
  }

  .mjj_box1 .title_right {
    color: #B8860B !important; /* 深黄色文本 */
  }

  .ztjj_box1 {
    background: #E0FFFF !important; /* 浅青色背景 */
  }

  .ztjj_box1 .title_right {
    color: #008B8B !important; /* 深青色文本 */
  }

  .schjj_box{
    background: <?=$changeimg['hvbfj8aokb']?>
  }
  .schjj_box .title_right{
    color: <?=$changeimg['hvbfjbx0lh']?>
  }
  .change_flex .data_box{
    padding: 0;
  }
  .change_flex .change_width4 {
    padding: 16px 0px 20px 2%;
  }
  .change_flex .change_width3{
    padding: 20px 0px 20px 5%;
  }

  .change_flex .change_width2:nth-child(1){
    padding: 5.8% 0px 29% 8%;
  }
  .change_flex .change_width2:nth-child(2){
    padding: 5.8% 0px 29% 8%;
  }
  .change_flex .change_width2:nth-child(3){
    padding: 13% 0px 33% 8%;
  }
  .change_flex .change_width2:nth-child(4){
    padding: 13% 0px 33% 8%;
  }

  <?php if ($glgs_type == 'hn80w73p9s' || $glgs_type == 'hqgkprquap'): ?>
  .change_flex .change_width2:nth-child(1){
    padding: 5.8% 0px 15% 8%;
  }
  .change_flex .change_width2:nth-child(2){
    padding: 5.8% 0px 15% 8%;
  }
  .change_flex .change_width2:nth-child(3){
    padding: 13% 0px 15% 8%;
  }
  .change_flex .change_width2:nth-child(4){
    padding: 13% 0px 15% 8%;
  }
  <?php endif ?>

  .change_flex .change_width2:nth-child(5){
    padding: 13% 0px 0% 8%;
  }
  .change_flex .change_width2:nth-child(6){
    padding: 13% 0px 0% 8%;
  }
  .change_width6{
    width: 31%;
  }
  .change_left{
    margin-left: 3.5%;
  }
  <?php if ($glgs_type != 'hn80w73p9s' && $glgs_type != 'hqgkprquap'): ?>
  
  .change_width6{
    width: 23%;
  }
  .change_left{
    margin-left: 3%;
  }
  <?php endif ?>
  .number p{
    display: inline;
  }
  .layui-layer-tips .layui-layer-content{
    background-color: #fff!important;
    color:#333!important;
  }

  .data_box a,.title_top a{
    pointer-events: none;
  }  
  .zhong_div{
    width: 32%;
  }
</style>
<div class="str_body change_flex">

  <!-- 总览 -->
  <div class="ztqk_box change_width1 change_flex_column" >
    <div class="title change_width1 title_top">
      <img src=" <?=$changeimg['hva5y2ns36']?> ">
      <p class="title_left" >总览</p>
      <a href="?/tzkbzs/view/about/<?=$tzqk?>" target="_blank">
        <p class="title_right" data-b2tips="点击查看全量基金"><span><?= 1345 * ($jj_data['touqian1'] ?: 0) ?></span>万</p>
      </a>
    </div>
    <div class="data_lists change_width1 change_flex" >
      <div class="data_box change_width4" >
        <div class="title">
          <span>投前项目总数量</span>
        </div>
        <div class="number">
          <a href="?/tzkbzs/view/about/<?=$tzqk?>" target="_blank">
            <p data-b2tips="点击查看全量基金"><?=$jj_data['touqian1']?:0?><span>个</span></p>
          </a>
        </div>
      </div>
      <div class="data_box change_width4" >
        <div class="title">
          <span>拓展项目总数量</span>
        </div>
        <div class="number">
          <a href="?/tzkbzs/view/about/<?=$tzqk?>" target="_blank">
            <p data-b2tips="点击查看全量基金"><?=$jj_data['touqian2']?:0?> <span>个</span></p>
          </a>
        </div>
      </div>
      <div class="data_box change_width4" >
        <div class="title">
          <span>其中立项项目总数量</span>
        </div>
        <div class="number">
          <a href="?/tzkbzs/view/about/<?=$tzqk?>" target="_blank">
            <p data-b2tips="点击查看全量基金"><?=$jj_data['touqian3']?:0?> <span>个</span></p>
          </a>
        </div>
      </div>
      <div class="data_box change_width4" >
        <div class="title">
          <span>其中重点项目总数量</span>
        </div>
        <div class="number">
          <a href="?/tzkbzs/view/about/<?=$tzqk?>" target="_blank">
            <p data-b2tips="点击查看全量基金"><?=$jj_data['touqian4']?:0?> <span>个</span></p>
          </a>
        </div>
      </div>
      <!-- <div class="data_box change_width4" >
        <div class="title">
          <span>医药行业项目总数量</span>
        </div>
        <div class="number">
          <a href="?/tzkbzsxm/view/about/<?=$tzqkxm?>" target="_blank">
            <p data-b2tips="点击查看全量基金项目"><?=$jj_data['touqian5']?:0?> <span>个</span></p>
          </a>
        </div>
      </div>
      <div class="data_box change_width4" >
        <div class="title">
          <span>自主开发项目总数量</span>
        </div>
        <div class="number">
          <a href="?/tzkbzsxm/view/about/<?=$tzqkxm?>" target="_blank">
            <p data-b2tips="点击查看全量基金项目"><?=$jj_data['touqian6']?:0?> <span>个</span></p>
          </a>
        </div>
      </div> -->
    </div>
  </div>
  <div class="change_width1" style="display: flex;margin-top:20px;">
    <!--股权项目  -->
    <div class="zyjj_box1 zhong_div change_flex_column change_width6" >
      <div class="title change_width1 title_top" >
        <img src=" <?=$changeimg['hva5z5dvtp']?> ">
        <p class="title_left" >股权项目</p>
        <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
          <p class="title_right" data-b2tips="点击查看自管基金"><span><?= 1345 * ($jj_data['xiangmu1'] ?: 0) ?></span>万</p>
        </a>
      </div>
      <div class="data_lists change_width1 change_flex" >
        <div class="data_box change_width3" >
          <div class="title">
            <span>投前项目数量</span>
          </div>
          <div class="number">
            <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
              <p data-b2tips="点击查看自管基金"><?=$jj_data['xiangmu1']?:0?><span>个</span></p>
            </a>
          </div>
        </div>
        
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中拓展项目数量</span>
          </div>
          <div class="number">
            <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
              <p data-b2tips="点击查看自管基金"><?=$jj_data['xiangmu2']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中立项项目数量</span>
          </div>
          <div class="number">
            <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
              <p data-b2tips="点击查看自管基金"><?=$jj_data['xiangmu3']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中重点项目数量</span>
          </div>
          <div class="number">
            <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
              <p data-b2tips="点击查看自管基金"><?=$jj_data['xiangmu4']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <!-- <div class="data_box change_width3" >
          <div class="title">
            <span>医药行业项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgjjxm?>" target="_blank">
              <p data-b2tips="点击查看自管基金项目"><?=$jj_data['xiangmu5']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>

        <div class="data_box change_width3" >
          <div class="title">
            <span>自主开发项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgjjxm?>" target="_blank">
              <p data-b2tips="点击查看自管基金项目"><?=$jj_data['xiangmu6']?:0?> <span>个</span></p>
            </a>
          </div>
        </div> -->
        
      </div>
    </div>
    <!-- 固定资产项目 -->
    <?php if ($glgs_type != 'hn80w73p9s' && $glgs_type != 'hqgkprquap'): ?>
    <div class="mjj_box1 zhong_div change_flex_column change_width6 change_left"  >
      <div class="title change_width1 title_top" >
        <!-- <img src="https://dev.vc800.net/?/file/img/ho6gxzo1bi_202411_hva61qfsnf.png"> -->
        <img src=" <?=$changeimg['hva5y2ns36']?> ">
        <p class="title_left">固定资产项目</p>
        <a href="<?=$zgmjjurl?>" target="_blank">
          <p class="title_right" data-b2tips="点击查看母基金"><span><?= 1345 * ($jj_data['guding1'] ?: 0) ?></span>万</p>
        </a>
      </div>
      <div class="data_lists change_width1 change_flex" >
        <div class="data_box change_width3" >
          <div class="title">
            <span>投前项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjurl?>" target="_blank">
              <p data-b2tips="点击查看母基金"><?=$jj_data['guding1']?:0?><span>个</span></p>
            </a>
          </div>
        </div>
        
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中拓展项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjurl?>" target="_blank">
              <p data-b2tips="点击查看母基金"><?=$jj_data['guding2']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中立项项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjurl?>" target="_blank">
              <p data-b2tips="点击查看母基金"><?=$jj_data['guding3']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中重点项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjurl?>" target="_blank">
              <p data-b2tips="点击查看母基金"><?=$jj_data['guding4']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <!-- <div class="data_box change_width3" >
          <div class="title">
            <span>医药行业项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjxm?>" target="_blank">
              <p data-b2tips="点击查看母基金项目"><?=$jj_data['guding5']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>

        <div class="data_box change_width3" >
          <div class="title">
            <span>自主开发项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjxm?>" target="_blank">
              <p data-b2tips="点击查看母基金项目"><?=$jj_data['guding6']?:0?> <span>个</span></p>
            </a>
          </div>
        </div> -->
      
      </div>
    </div>
    <?php endif ?>
    <!-- 基金项目 -->
    <div class="ztjj_box1 zhong_div change_flex_column change_width6 change_left" >
      <div class="title change_width1 title_top" >
        <!-- <img src="https://dev.vc800.net/?/file/img/ho6gxzo1bi_202411_hva61wad9k.png"> -->
        <img src=" <?=$changeimg['hva5yqosz8']?> ">
        <p class="title_left">基金项目</p>
        <a href="<?=$zgztjjurl?>" target="_blank">
          <p class="title_right" data-b2tips="点击查看直投基金"><span><?= 1345 * ($jj_data['jijin1'] ?: 0) ?></span>万</p>
        </a>
      </div>
      <div class="data_lists change_width1 change_flex" >
        <div class="data_box change_width3" >
          <div class="title">
            <span>投前项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jijin1']?:0?><span>个</span></p>
            </a>
          </div>
        </div>
        
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中拓展项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jijin2']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中立项项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jijin3']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中重点项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jijin4']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <!-- <div class="data_box change_width3" >
          <div class="title">
            <span>医药行业项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjxm?>" target="_blank">
              <p data-b2tips="点击查看直投基金项目"><?=$jj_data['jijin5']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>

        <div class="data_box change_width3" >
          <div class="title">
            <span>自主开发项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjxm?>" target="_blank">
              <p data-b2tips="点击查看直投基金项目"><?=$jj_data['jijin6']?:0?> <span>个</span></p>
            </a>
          </div>
        </div> -->
        
      </div>
    </div>
  </div>
  <!-- 中间部分  -->
  <div class="change_width1" style="display: flex;margin-top:20px;">
    <!--长期股权投资  -->
    <div class="zyjj_box change_flex_column change_width6" >
      <div class="title change_width1 title_top" >
        <img src=" <?=$changeimg['hva5yglk62']?> ">
        <p class="title_left" >长期股权投资</p>
        <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
          <p class="title_right" data-b2tips="点击查看自管基金"><span><?= 1345 * ($jj_data['xiangmu1'] ?: 0) ?></span>万</p>
        </a>
      </div>
      <div class="data_lists change_width1 change_flex" >
        <div class="data_box change_width3" >
          <div class="title">
            <span>投前项目数量</span>
          </div>
          <div class="number">
            <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
              <p data-b2tips="点击查看自管基金"><?=$jj_data['xiangmu1']?:0?><span>个</span></p>
            </a>
          </div>
        </div>
        
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中拓展项目数量</span>
          </div>
          <div class="number">
            <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
              <p data-b2tips="点击查看自管基金"><?=$jj_data['xiangmu2']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中立项项目数量</span>
          </div>
          <div class="number">
            <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
              <p data-b2tips="点击查看自管基金"><?=$jj_data['xiangmu3']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中重点项目数量</span>
          </div>
          <div class="number">
            <a href="?/tzkbzs/view/about/<?=$zgjj?>" target="_blank">
              <p data-b2tips="点击查看自管基金"><?=$jj_data['xiangmu4']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <!-- <div class="data_box change_width3" >
          <div class="title">
            <span>医药行业项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgjjxm?>" target="_blank">
              <p data-b2tips="点击查看自管基金项目"><?=$jj_data['xiangmu5']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>

        <div class="data_box change_width3" >
          <div class="title">
            <span>自主开发项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgjjxm?>" target="_blank">
              <p data-b2tips="点击查看自管基金项目"><?=$jj_data['xiangmu6']?:0?> <span>个</span></p>
            </a>
          </div>
        </div> -->
        
      </div>
    </div>
    <!-- 固定资产投资 -->
    <?php if ($glgs_type != 'hn80w73p9s' && $glgs_type != 'hqgkprquap'): ?>
    <div class="mjj_box change_flex_column change_width6 change_left"  >
      <div class="title change_width1 title_top" >
        <!-- <img src="https://dev.vc800.net/?/file/img/ho6gxzo1bi_202411_hva61qfsnf.png"> -->
        <img src=" <?=$changeimg['hva5yqosz8']?> ">
        <p class="title_left">固定资产投资</p>
        <a href="<?=$zgmjjurl?>" target="_blank">
          <p class="title_right" data-b2tips="点击查看母基金"><span><?= 1345 * ($jj_data['guding1'] ?: 0) ?></span>万</p>
        </a>
      </div>
      <div class="data_lists change_width1 change_flex" >
        <div class="data_box change_width3" >
          <div class="title">
            <span>投前项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjurl?>" target="_blank">
              <p data-b2tips="点击查看母基金"><?=$jj_data['guding1']?:0?><span>个</span></p>
            </a>
          </div>
        </div>
        
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中拓展项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjurl?>" target="_blank">
              <p data-b2tips="点击查看母基金"><?=$jj_data['guding2']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中立项项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjurl?>" target="_blank">
              <p data-b2tips="点击查看母基金"><?=$jj_data['guding3']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中重点项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjurl?>" target="_blank">
              <p data-b2tips="点击查看母基金"><?=$jj_data['guding4']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <!-- <div class="data_box change_width3" >
          <div class="title">
            <span>医药行业项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjxm?>" target="_blank">
              <p data-b2tips="点击查看母基金项目"><?=$jj_data['guding5']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>

        <div class="data_box change_width3" >
          <div class="title">
            <span>自主开发项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgmjjxm?>" target="_blank">
              <p data-b2tips="点击查看母基金项目"><?=$jj_data['guding6']?:0?> <span>个</span></p>
            </a>
          </div>
        </div> -->
      
      </div>
    </div>
    <?php endif ?>
    <!-- 土地投资 -->
    <div class="zgzjj_box  change_flex_column change_width6 change_left"   >
      <div class="title change_width1 title_top" >
        <!-- <img src="https://dev.vc800.net/?/file/img/ho6gxzo1bi_202411_hva61swr8y.png"> -->
        <img src=" <?=$changeimg['hva5yyr7md']?> ">
        <p class="title_left">土地投资</p>
        <a href="<?=$zgzjjurl?>" target="_blank">
          <p class="title_right" data-b2tips="点击查看自管子基金"><span>1344</span>万</p>
        </a>
      </div>
      <div class="data_lists change_width1 change_flex" >
        <div class="data_box change_width3" >
          <div class="title">
            <span>投前项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgzjjurl?>" target="_blank">
              <p data-b2tips="点击查看自管子基金"><?=$jj_data['tudi1']?:0?><span>个</span></p>
            </a>
          </div>
        </div>
        
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中拓展项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgzjjurl?>" target="_blank">
              <p data-b2tips="点击查看自管子基金"><?=$jj_data['tudi2']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中立项项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgzjjurl?>" target="_blank">
              <p data-b2tips="点击查看自管子基金"><?=$jj_data['tudi3']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中重点项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgzjjurl?>" target="_blank">
              <p data-b2tips="点击查看自管子基金"><?=$jj_data['tudi4']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <!-- <div class="data_box change_width3" >
          <div class="title">
            <span>医药行业项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgzjjxm?>" target="_blank">
              <p data-b2tips="点击查看自管子基金项目"><?=$jj_data['tudi5']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>

        <div class="data_box change_width3" >
          <div class="title">
            <span>自主开发项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgzjjxm?>" target="_blank">
              <p data-b2tips="点击查看自管子基金项目"><?=$jj_data['tudi6']?:0?> <span>个</span></p>
            </a>
          </div>
        </div> -->
      </div>
    </div>
    <!-- 基金投资 -->
    <div class="ztjj_box  change_flex_column change_width6 change_left" >
      <div class="title change_width1 title_top" >
        <!-- <img src="https://dev.vc800.net/?/file/img/ho6gxzo1bi_202411_hva61wad9k.png"> -->
        <img src=" <?=$changeimg['hva5z5dvtp']?> ">
        <p class="title_left">基金投资</p>
        <a href="<?=$zgztjjurl?>" target="_blank">
          <p class="title_right" data-b2tips="点击查看直投基金"><span><?= 1345 * ($jj_data['jijin1'] ?: 0) ?></span>万</p>
        </a>
      </div>
      <div class="data_lists change_width1 change_flex" >
        <div class="data_box change_width3" >
          <div class="title">
            <span>投前项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jijin1']?:0?><span>个</span></p>
            </a>
          </div>
        </div>
        
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中拓展项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jijin2']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中立项项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jijin3']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中重点项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jijin4']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <!-- <div class="data_box change_width3" >
          <div class="title">
            <span>医药行业项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjxm?>" target="_blank">
              <p data-b2tips="点击查看直投基金项目"><?=$jj_data['jijin5']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>

        <div class="data_box change_width3" >
          <div class="title">
            <span>自主开发项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjxm?>" target="_blank">
              <p data-b2tips="点击查看直投基金项目"><?=$jj_data['jijin6']?:0?> <span>个</span></p>
            </a>
          </div>
        </div> -->
        
      </div>
    </div>

    <!-- 金融市场投资 -->
    <div class="schjj_box  change_flex_column change_width6 change_left" >
      <div class="title change_width1 title_top" >
        <!-- <img src="https://dev.vc800.net/?/file/img/ho6gxzo1bi_202411_hva61wad9k.png"> -->
        <img src=" <?=$changeimg['hva5zflk18']?> ">
        <p class="title_left">金融市场投资</p>
        <a href="<?=$zgztjjurl?>" target="_blank">
          <p class="title_right" data-b2tips="点击查看直投基金"><span><?= 1345 * ($jj_data['jinrong1'] ?: 0) ?></span>万</p>
        </a>
      </div>
      <div class="data_lists change_width1 change_flex" >
        <div class="data_box change_width3" >
          <div class="title">
            <span>投前项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jinrong1']?:0?><span>个</span></p>
            </a>
          </div>
        </div>
        
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中拓展项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jinrong2']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中立项项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jinrong3']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <div class="data_box change_width3" >
          <div class="title">
            <span>其中重点项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjurl?>" target="_blank">
              <p data-b2tips="点击查看直投基金"><?=$jj_data['jinrong4']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>
        <!-- <div class="data_box change_width3" >
          <div class="title">
            <span>医药行业项目数量</span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjxm?>" target="_blank">
              <p data-b2tips="点击查看直投基金项目"><?=$jj_data['jinrong5']?:0?> <span>个</span></p>
            </a>
          </div>
        </div>

        <div class="data_box change_width3" >
          <div class="title">
            <span>自主开发项目数量 </span>
          </div>
          <div class="number">
            <a href="<?=$zgztjjxm?>" target="_blank">
              <p data-b2tips="点击查看直投基金项目"><?=$jj_data['jinrong6']?:0?> <span>个</span></p>
            </a>
          </div>
        </div> -->
        
      </div>
    </div>
  </div>
</div>


<div class="chart_body">
  <div class="chart_list_box">
    <?=$chart_html?>
  </div>
</div>