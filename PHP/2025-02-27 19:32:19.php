<?php

load('c/entity', false);
class hnic_new_str_shyy extends entity
{
  function __construct()
  {
    parent::__construct();
    $this->em = load("m/entity_m");
    $this->cm = load("m/config_m");
  }

  function index(){
    $sid = WEBSID;
    //图表标题
    $glgs = $this->em->get(" and `sid`='$sid' and `type`='glgs' and `del`=0 ");
    $glgs_data = [];
    $profile = load('m/space_usr_m')->profile($sid, UID);

    if (in_array('投资集团',$profile['role_label']) || in_array('创新集团',$profile['role_label']) || in_array('博士后工作站',$profile['role_label']) ) {
      $glgs_data['cxjt'] = '创新集团';
    }
    foreach ($glgs as $k => $v) {
      $v['data'] = _decode($v['data']);
      if(in_array($v['name'],['国裕高华','中金汇融','中原海云','汇科基金','汇融基金','雨韵和丰'])){
        if (in_array('投资集团',$profile['role_label']) || in_array('创新集团',$profile['role_label']) || in_array('博士后工作站',$profile['role_label']) ) {
          $glgs_data[$v['uuid']] = $v['name'];
        }else{
          if (in_array($v['name'],$profile['role_label']) || (in_array('河南创投', $profile['role_label']) && $v['name'] == '雨韵和丰')  ) {
            $glgs_data[$v['uuid']] = $v['name'];
          }
        }
      }
    }
    $glgs_data['other'] = '其他';
    $param['glgs_data'] = $glgs_data;

    $glgs_data = [];
    $lists = $this->em->get(" and `sid`='$sid' and `del`=0 and `type`='stockinvestment' ");

    foreach ($lists as $k => $v) {
      $glgs_data[$v['name']] = $v['name'];
    }
    $param['glgs_data'] = $glgs_data;

    $this->display("v/hnic_new_str_shyy/index",$param);
  }
               
  function change_body()
  {
    $sid = WEBSID;
    $time_type = $_POST['time_type'];
    $glgs_type = $_POST['glgs_type'];

    if($time_type=='cl'){
      $type = 'stockinvestment';
    }else if($time_type=='zl'){
      $type = 'addinvestment';
    }

    // 获取图片配置
    $changeimg = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'new_str_img' ORDER BY `id` DESC ")[0];
    $param['changeimg'] = _decode($changeimg['data']);


    $elem = $this->em->get(" and `sid`='$sid' and `del`=0 and `type`='$type' and `name`='$glgs_type' ")[0];

    $elem['data'] = _decode($elem['data']);

    $param['jj_data'] = $elem['data'];
    $html = view("v/hnic_new_str_shyy/change_body",$param,true);
    ajax_return(['ok'=>1,'html'=>$html]);



    $profile = load('m/space_usr_m')->profile($sid, UID);

    $glgs = $this->em->get(" and `sid`='$sid' and `type`='glgs' and `del`=0 ");
    $glgs_uuid = [];
    // $glgs_all_uuid = [];
    $hwnv8p511q = '';
    $glgs_name = [];
    foreach ($glgs as $k => $v) {
      $v['data'] = _decode($v['data']);

      // $glgs_all_uuid[] = $v['uuid'];

      if($glgs_type=='all'){
        
        if (in_array('投资集团',$profile['role_label']) || in_array('创新集团',$profile['role_label']) || in_array('博士后工作站',$profile['role_label']) ) {
          $glgs_uuid[] = $v['uuid'];
          $glgs_name[] = $v['name'];
        }else{
          if (in_array($v['name'],$profile['role_label']) || ( in_array('河南创投', $profile['role_label']) && $v['name'] == '雨韵和丰' ) ) {
            $glgs_uuid[] = $v['uuid'];
            $glgs_name[] = $v['name'];
          }
        }
        $hwnv8p511q = '总览';
      }else if($glgs_type=='other'){
        if(!in_array($v['name'],['国裕高华','中金汇融','中原海云','汇科基金','汇融基金','雨韵和丰'])){
          $glgs_uuid[] = $v['uuid'];
          $glgs_name[] = $v['name'];
        }
        $hwnv8p511q = '其他';
      }else if($glgs_type == 'cxjt'){
        if (in_array($v['name'],['中金汇融','汇融基金','汇科基金','中原海云','雨韵和丰'])) {
          $glgs_uuid[] = $v['uuid'];
          $glgs_name[] = $v['name'];
        }
        $hwnv8p511q = '创新集团';
      }else if($glgs_type==$v['uuid']){
        $glgs_uuid[] = $v['uuid'];
        $glgs_name[] = $v['name'];
        $hwnv8p511q = $v['name'];
      }
    }


    // 当前年份
    $this_year = date('Y',time());

    //基金数据
    $jj_data = [];

    //fund_zg数据
    $fund_zg_lists = $this->em->get(" and `sid`='$sid' and `type`='fund_zg' and `del`=0  ",0,9999);

    // $fund_zg_uuid = [];

    foreach ($fund_zg_lists as $k => $v) {
      $v['data'] = _decode($v['data']);

      $year = date('Y',strtotime($v['data']['clsj']));

      // $fund_zg_uuid[] = $v['uuid'];
      if (!empty($v['data']['jjfamc']) && $v['data']['sfwcl'] != '否' ) {
        continue;
      }

      if($time_type=='zl'&&$year!=$this_year){
        continue;
      }
      if(!in_array($v['data']['cyzt'],$glgs_uuid)){
        continue;
      }

      // 自管基金情况
      if ( in_array($v['data']['jjzt'], ['已设立','投资期','退出期','期满未清算','期满延长期','已退出','清算期']) ) {
        // 基金总数量
        $jj_data['zgjjzsl']++;
        // 累计认缴规模
        $jj_data['zgljrjgm'] += round($v['data']['jjrjgm']/100000000,2);
        // 累计实缴规模
        $jj_data['zgljsjgm'] += round($v['data']['jjsjgm']/100000000,2);
        // 集团认缴规模
        $jj_data['zgjtrjgm'] += round($v['data']['jtrjje']/100000000,2);
        // 集团实缴规模
        $jj_data['zgjtsjgm'] += round($v['data']['jtsjje']/100000000,2);
        // 母基金情况
        if ($v['data']['jjlx'] == '母基金') {
          // 基金总数量
          $jj_data['mjjjjzsl']++;
          // 累计认缴规模
          $jj_data['mjjljrjgm'] += round($v['data']['jjrjgm']/100000000,2);
          // 累计实缴规模
          $jj_data['mjjljsjgm'] += round($v['data']['jjsjgm']/100000000,2);
          // 集团认缴规模
          $jj_data['mjjjtrjgm'] += round($v['data']['jtrjje']/100000000,2);
          // 集团实缴规模
          $jj_data['mjjjtsjgm'] += round($v['data']['jtsjje']/100000000,2);
        }
        // 直投基金
        if ($v['data']['jjlx'] == '直投基金') {
          // 基金总数量
          $jj_data['ztjjjjzsl']++;
          // 累计认缴规模
          $jj_data['ztjjljrjgm'] += round($v['data']['jjrjgm']/100000000,2);
          // 累计实缴规模
          $jj_data['ztjjljsjgm'] += round($v['data']['jjsjgm']/100000000,2);
          // 集团认缴规模
          $jj_data['ztjjjtrjgm'] += round($v['data']['jtrjje']/100000000,2);
          // 集团实缴规模
          $jj_data['ztjjjtsjgm'] += round($v['data']['jtsjje']/100000000,2);
        }
      }

    }


    //fund数据
    $fund_lists = $this->em->get(" and `sid`='$sid' and `type`='fund' and `del`=0  ",0,9999);

    $fund_uuid = [];

    foreach ($fund_lists as $k => $v) {
      $v['data'] = _decode($v['data']);

      $year = date('Y',strtotime($v['data']['sign_time']));

      $fund_uuid[] = $v['uuid'];

      if (!empty($v['data']['jjfamc']) && $v['data']['sfwcl'] != '否' ) {
        continue;
      }
      
      if($time_type=='zl'&&$year!=$this_year){
        continue;
      }
      if(!in_array($v['data']['xmssdw'],$glgs_uuid)){
        continue;
      }

      // 自管基金情况 / 自管子基金情况
      if ($v['data']['jjfx'] == '自主管理类子基金') {
        // 基金总数量
        $jj_data['zgjjzsl']++;
        // 累计认缴规模
        $jj_data['zgljrjgm'] += round($v['data']['jjrjgm']/100000000,2);
        // 累计实缴规模
        $jj_data['zgljsjgm'] += round($v['data']['jjsjgm']/100000000,2);
        // 集团认缴规模
        $jj_data['zgjtrjgm'] += round($v['data']['jtjjrjje1']/100000000,2);
        // 集团实缴规模
        $jj_data['zgjtsjgm'] += round($v['data']['jtjjsjje1']/100000000,2);

        // 基金总数量
        $jj_data['zgzjjjjzsl']++;
        // 累计认缴规模
        $jj_data['zgzjjljrjgm'] += round($v['data']['jjrjgm']/100000000,2);
        // 累计实缴规模
        $jj_data['zgzjjljsjgm'] += round($v['data']['jjsjgm']/100000000,2);
        // 集团认缴规模
        $jj_data['zgzjjjtrjgm'] += round($v['data']['jtrjje']/100000000,2);//?
        // 集团实缴规模
        $jj_data['zgzjjjtsjgm'] += round($v['data']['jtsjje']/100000000,2);//?
      }
      // 市场化基金情况
      if ( in_array($v['data']['jjfx'],['市场化子基金','市场化直投基金']) && in_array($v['data']['state_label'],['投后','退出']) ) {
        // 基金总数量
        $jj_data['scjjzsl']++;
        // 累计认缴规模
        $jj_data['scljrjgm'] += round($v['data']['jjrjgm']/100000000,2);
        // 累计实缴规模
        $jj_data['scljsjgm'] += round($v['data']['jjsjgm']/100000000,2);
        // 集团认缴规模
        $jj_data['scjtrjgm'] += round($v['data']['jtrjje']/100000000,2);
        // 集团实缴规模
        $jj_data['scjtsjgm'] += round($v['data']['jtsjje']/100000000,2);

      }
    }


    // company数据
    $company_lists = $this->em->get(" and `sid`='$sid' and `type`='company' and `del`=0  ",0,9999);

    foreach ($company_lists as $k => $v) {
      $v['data'] = _decode($v['data']);

      $year = date('Y',strtotime($v['data']['tzsj']));

      if($time_type=='zl'&&$year!=$this_year){
        continue;
      }
      if(!in_array($v['data']['xmssdw'],$glgs_uuid)){
        continue;
      }

      if (in_array($v['data']['company_state'],['投后管理','项目退出'])) {
        // 自管基金情况
        if ($v['data']['xmlx2'] == '自主管理类项目') {
          // 项目总数量
          $jj_data['zgxmzsl']++;
          // 累计退出金额
          if ($time_type == 'zl' && $year == $this_year) {
            if ($v['data']['latest_exit_date'] == $this_year) {
              $jj_data['zgljtcje'] += round($v['data']['total_exit_accumulative_amount']/100000000,2);
            }
          }else{
            $jj_data['zgljtcje'] += round($v['data']['total_exit_accumulative_amount']/100000000,2);
          }
        }
        // 母基金情况
        if ($v['data']['tzrxz'] == '母基金') {
          // 项目总数量
          $jj_data['mjjxmzsl']++;
          // 累计退出金额                                                                    
          if ($time_type == 'zl' && $year == $this_year) {
            if ($v['data']['latest_exit_date'] == $this_year) {
              $jj_data['mjjljtcje'] += round($v['data']['total_exit_accumulative_amount']/100000000,2);
            }
          }else{
            $jj_data['mjjljtcje'] += round($v['data']['total_exit_accumulative_amount']/100000000,2);
          }
        }
        // 直投基金
        if ($v['data']['tzrxz'] == '直投基金') {
          // 项目总数量
          $jj_data['ztjjxmzsl']++;
          // 累计退出金额
          if ($time_type == 'zl' && $year == $this_year) {
            if ($v['data']['latest_exit_date'] == $this_year) {
              $jj_data['ztjjljtcje'] += round($v['data']['tcze']/100000000,2);
            }
          }else{
            $jj_data['ztjjljtcje'] += round($v['data']['tcze']/100000000,2);
          }
        }
        // 自管子基金情况
        if ($v['data']['tzrxz'] == '自主管理类子基金') {
          // 项目总数量
          $jj_data['zgzjjxmzsl']++;
          // 累计退出金额
          if ($time_type == 'zl' && $year == $this_year) {
            if ($v['data']['latest_exit_date'] == $this_year) {
              $jj_data['zgzjjljtcje'] += round($v['data']['total_exit_accumulative_amount']/100000000,2);
            }
          }else{
            $jj_data['zgzjjljtcje'] += round($v['data']['total_exit_accumulative_amount']/100000000,2);
          }
        }
      }

      if ($v['data']['xmlx2'] == '市场化基金项目') {
        // 项目总数量
        $jj_data['scxmzsl']++;
        // 累计退出金额
        if ($time_type == 'zl' && $year == $this_year) {
          if ($v['data']['latest_exit_date'] == $this_year) {
            $jj_data['scljtcje'] += round($v['data']['total_exit_accumulative_amount']/100000000,2);
          }
        }else{
          $jj_data['scljtcje'] += round($v['data']['total_exit_accumulative_amount']/100000000,2);
        }
      }

    }

    // 总体情况
      // 基金总数量
      $jj_data['ztjjzsl'] = $jj_data['zgjjzsl'] + $jj_data['scjjzsl'];
      // 累计认缴规模
      $jj_data['ztljrjgm'] = $jj_data['touqian1'] * 1483.25;
      // 累计实缴规模
      $jj_data['ztljsjgm'] = $jj_data['zgljsjgm'] + $jj_data['scljsjgm'];
      // 集团认缴规模
      $jj_data['ztjtrjgm'] = $jj_data['zgjtrjgm'] + $jj_data['scjtrjgm'];
      // 集团实缴规模
      $jj_data['ztjtsjgm'] = $jj_data['zgjtsjgm'] + $jj_data['scjtsjgm'];
      // 项目总数量
      $jj_data['ztxmzsl'] = $jj_data['zgxmzsl'] + $jj_data['scxmzsl'];
      // 累计退出金额
      $jj_data['ztljtcje'] = $jj_data['zgljtcje'] + $jj_data['scljtcje'];


    $chart_html = $this->change_chart($glgs_type,$time_type);
    $param['jj_data'] = $jj_data;
    $param['chart_html'] = $chart_html;
    $param['glgs_type'] = $glgs_type;
    // 获取图片配置
    $changeimg = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'new_str_img' ORDER BY `id` DESC ")[0];
    $param['changeimg'] = _decode($changeimg['data']);

    $hwnwrmwrck = $time_type == 'zl' ? '是' : '否';
    // url跳转配置
    $tzkbzs = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'tzkbzs' AND `data`->>'$.hwnv8p511q' = '$hwnv8p511q' AND `data`->>'$.hwnwrmwrck' = '$hwnwrmwrck' ");
    // 总体
    $param['tzqk'] = '';
    // 自管
    $param['zgjj'] = '';
    foreach ($tzkbzs as $key => $value) {
      $value['data'] = _decode($value['data']);
      if ($value['data']['hwnv28j8sc'] == '是') {
        $param['tzqk'] = $value['uuid'];
      }else{
        $param['zgjj'] = $value['uuid'];
      }
    }
    // 
    $tzkbzsxm = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'tzkbzsxm' AND `data`->>'$.hwnv8p511q' = '$hwnv8p511q' AND `data`->>'$.hwnwrmwrck' = '$hwnwrmwrck' ");
    // 
    $param['tzqkxm'] = '';
    foreach ($tzkbzsxm as $key => $value) {
      $value['data'] = _decode($value['data']);
      $param['tzqkxm'] = $value['uuid'];
    }
    $glgs_name = implode(',',$glgs_name);
    // 自管母基金
    $param['zgmjjurl'] = "?/fund_zg_mjj_sl_tzkb#cyzt=$glgs_name";
    // 自管子基金
    $param['zgzjjurl'] = "?/fund_yd_xin_sl_tzkb#xmssdw=$glgs_name";
    // 自管直投基金
    $param['zgztjjurl'] = "?/fundd_sl_tzkb#cyzt=$glgs_name";
    // 市场化基金
    $param['schjjurl'] = "?/fund_xin_sl_tzkb#xmssdw=$glgs_name";
    if ($time_type=='zl') {
      $times = date('Y').'-01-01 - '.date(Y).'-12-31';
      // 自管母基金
      $param['zgmjjurl'] = "?/fund_zg_mjj_sl_tzkb#cyzt=$glgs_name&clsj=$times";
      // 自管子基金
      $param['zgzjjurl'] = "?/fund_yd_xin_sl_tzkb#xmssdw=$glgs_name&sign_time=$times";
      // 自管直投基金
      $param['zgztjjurl'] = "?/fundd_sl_tzkb#cyzt=$glgs_name&clsj=$times";
      // 市场化基金
      $param['schjjurl'] = "?/fund_xin_sl_tzkb#xmssdw=$glgs_name&sign_time=$times";
    }

    // 自管基金项目总数量
    $param['zgjjxm'] = "?/company_after_zs#xmlx2=自主管理类项目&xmssdw=$glgs_name";
    // 自管母基金
    $param['zgmjjxm'] = "?/company_after_zs#tzrxz=母基金&xmssdw=$glgs_name";
    // 自管子基金
    $param['zgzjjxm'] = "?/company_after_zs#tzrxz=自主管理类子基金&xmssdw=$glgs_name";
    // 自管直投基金
    $param['zgztjjxm'] = "?/company_after_zt#xmssdw=$glgs_name";
    // 市场化基金
    $param['schjj'] = "?/subcompany3_zs#xmlx2=市场化基金项目&xmssdw=$glgs_name";
    if ($time_type=='zl') {
      $times = date('Y').'-01-01 - '.date(Y).'-12-31';
      // 自管基金项目总数量
      $param['zgjjxm'] = "?/company_after_zs#xmlx2=自主管理类项目&xmssdw=$glgs_name&tzsj=$times";
      // 自管母基金
      $param['zgmjjxm'] = "?/company_after_zs#tzrxz=母基金&xmssdw=$glgs_name&tzsj=$times";
      // 自管子基金
      $param['zgzjjxm'] = "?/company_after_zs#tzrxz=自主管理类子基金&xmssdw=$glgs_name&tzsj=$times";
      // 自管直投基金
      $param['zgztjjxm'] = "?/company_after_zt#xmssdw=$glgs_name&tzsj=$times";
      // 市场化基金
      $param['schjj'] = "?/subcompany3_zs#xmlx2=市场化基金项目&xmssdw=$glgs_name&tzsj=$times";
    }



    $html = view("v/hnic_new_str_shyy/change_body",$param,true);
    ajax_return(['ok'=>1,'html'=>$html]);
  } 

  function change_chart($glgs_type,$time_type)
  {
    $sid = WEBSID;

    $profile = load('m/space_usr_m')->profile($sid, UID);

    $glgs = $this->em->get(" and `sid`='$sid' and `type`='glgs' and `del`=0 ");
    $glgs_uuid = [];
    $glgs_all_uuid = [];
    foreach ($glgs as $k => $v) {
      $v['data'] = _decode($v['data']);

      $glgs_all_uuid[] = $v['uuid'];

      if($glgs_type=='all'){
        
        if (in_array('投资集团',$profile['role_label']) || in_array('创新集团',$profile['role_label']) || in_array('博士后工作站',$profile['role_label']) ) {
          $glgs_uuid[] = $v['uuid'];
        }else{
          if (in_array($v['name'],$profile['role_label']) || ( in_array('河南创投', $profile['role_label']) && $v['name'] == '雨韵和丰' ) ) {
            $glgs_uuid[] = $v['uuid'];
          }
        }

      }else if($glgs_type=='other'){
        if(!in_array($v['name'],['国裕高华','中金汇融','中原海云','汇科基金','汇融基金','雨韵和丰'])){
          $glgs_uuid[] = $v['uuid'];
        }
      }else if($glgs_type == 'cxjt'){
        if (in_array($v['name'],['中金汇融','汇融基金','汇科基金','中原海云','雨韵和丰'])) {
          $glgs_uuid[] = $v['uuid'];
        }
      }else if($glgs_type==$v['uuid']){
        $glgs_uuid[] = $v['uuid'];
      }
    }

    // 当前年份
    $this_year = date('Y',time());

    //自管基金收益情况（废弃）
    // 行业图表
    $chart1 = [];
    //各管理公司项目投资数量/金额
    $chart2 = [];
    //各管理公司基金投资数量/金额
    $chart3 = [];
    //自管基金类型
    $chart4 = [];
    //子基金注册地
    $chart5 = [];
    //市场化子基金返投类型
    $chart6 = [];
    //市场化基金底层项目行业分布
    $chart7 = [];
    //直投项目所在地
    $chart8 = [];
    //直投项目行业分布
    $chart9 = [];
    //项目来源
    $chart10 = [];
    //战略性新兴产业分布
    $chart11 = [];


    //fund_zg数据
    $fund_zg_lists = $this->em->get(" and `sid`='$sid' and `type`='fund_zg' and `del`=0  ",0,9999);

    $fund_zg_uuid = [];

    foreach ($fund_zg_lists as $k => $v) {
      $v['data'] = _decode($v['data']);

      $year = date('Y',strtotime($v['data']['fund_establish_date']));

      $fund_zg_uuid[] = $v['uuid'];

      if($time_type=='zl'&&$year!=$this_year){
        continue;
      }

      if(!in_array($v['data']['cyzt'],$glgs_uuid)){
        continue;
      }

      // if($v['data']['jjxz']=='自管类'){
      //   $chart1[$v['data']['name']]['moc'] = $v['data']['moc']?:0; 
      //   $chart1[$v['data']['name']]['irr'] = $v['data']['irr']?:0; 
      // }

      if(in_array($v['data']['jjlx'],['母基金','直投基金','引导基金'])){
        $chart4[$v['data']['jjlx']] ++;
      }
    }

    // company数据
    $company_lists = $this->em->get(" and `sid`='$sid' and `type`='company' and `del`=0  ",0,9999);

    foreach ($company_lists as $k => $v) {
      $v['data'] = _decode($v['data']);

      $year = date('Y',strtotime($v['data']['tzsj']));

      if($time_type=='zl'&&$year!=$this_year){
        continue;
      }

      if(in_array($v['data']['company_state'],['投后管理','项目退出'])&&in_array($v['data']['xmssdw'],$glgs_all_uuid)){
        $chart2[$v['data']['xmssdw_label']]['num'] ++;
        $chart2[$v['data']['xmssdw_label']]['money'] += round($v['data']['tzje']/100000000,2);
      }

      if(!in_array($v['data']['xmssdw'],$glgs_uuid)){
        continue;
      }

      if(in_array($v['data']['company_state'],['投后管理','项目退出'])){
        if($v['data']['xmlx2']=='市场化基金项目'){
          // $v['data']['sshy_label']&&$chart7[$v['data']['sshy_label']] ++;
        }

        $v['data']['province_label'] && $chart8['province'][$v['data']['province_label']] ++;
        if($v['data']['province_label']=='河南省'&&$v['data']['city_label']){
          $chart8['city'][$v['data']['city_label']] ++;
        }

        // $v['data']['sshy_label']&&$chart9[$v['data']['sshy_label']] ++;
        // $v['data']['source_label']&&$chart10[$v['data']['source_label']] ++;
        $v['data']['zlxcy_label']&&$chart11[$v['data']['zlxcy_label']] ++;

        if ($v['data']['invest_subject_primary_industry_label']) {
          $chart1[$v['data']['invest_subject_primary_industry_label']]['num']++;
          $chart1[$v['data']['invest_subject_primary_industry_label']]['money'] += round($v['data']['tzje']/100000000,2);
        }
      }
    }

    //fund数据
    $fund_lists = $this->em->get(" and `sid`='$sid' and `type`='fund' and `del`=0  ",0,9999);

    $fund_uuid = [];

    foreach ($fund_lists as $k => $v) {
      $v['data'] = _decode($v['data']);

      $year = date('Y',strtotime($v['data']['tjsj']));

      $fund_uuid[] = $v['uuid'];

      if($time_type=='zl'&&$year!=$this_year){
        continue;
      }

      if(in_array($v['data']['state'],['投后','退出'])&&in_array($v['data']['xmssdw'],$glgs_all_uuid)){
        $chart3[$v['data']['xmssdw_label']]['num'] ++;
        $chart3[$v['data']['xmssdw_label']]['money'] += $v['data']['ljsjczje']/100000000;
      }

      if(!in_array($v['data']['xmssdw'],$glgs_uuid)){
        continue;
      }

      if(in_array($v['data']['state'],['投后','退出'])){
        $v['data']['province_label'] && $chart5['province'][$v['data']['province_label']] ++;
        if($v['data']['province_label']=='河南省'&&$v['data']['city_label']){
          $chart5['city'][$v['data']['city_label']] ++;
        }

        // if($v['data']['jjfx']=='市场化基金项目'){
        //   $chart6[$v['data']['ftfs_label']]['num'] ++;
        //   $chart6[$v['data']['ftfs_label']]['money'] += $v['data']['ftje']/100000000;
        // }
      }
      if ($v['data']['jjfx'] == '自主管理类子基金') {
        $chart4['子基金'] ++;
      }
    }

    //自管基金收益情况
    $param['chart1'] = $chart1;
    //各管理公司项目投资数量/金额
    $param['chart2'] = $chart2;
    //各管理公司基金投资数量/金额
    $param['chart3'] = $chart3;
    //自管基金类型
    $param['chart4'] = $chart4;
    //子基金注册地
    $param['chart5'] = $chart5;
    //市场化子基金返投类型
    $param['chart6'] = $chart6;
    //市场化基金底层项目行业分布
    $param['chart7'] = $chart7;
    //直投项目所在地
    $param['chart8'] = $chart8;
    //直投项目行业分布
    $param['chart9'] = $chart9;
    //项目来源
    $param['chart10'] = $chart10;
    //战略性新兴产业分布
    $param['chart11'] = $chart11;

    //dump($param);


    $param['glgs_type'] = $glgs_type;

    $html = view("v/hnic_new_str_shyy/change_chart",$param,true);

    return $html;
  }
} 