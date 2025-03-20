<?php
class dalton extends server_script{
    function __construct()
    {
      $this->em = load("m/entity_m")->ban_notify();
      $this->cm = load("m/config_m");
    }

    /**
     * @author dy
     * task03243
     * @description 每周日晚上5点自动新增模块，并代码统计各人员bp数量，完成后代码邮件通知brenda。
     */
    function email_to_brenda($sid){
        if(!$sid)return;
        define('WEBSID',$sid);
        define('UID','ggjjny3f1y');//ggjjny3f1y
        $send_name='徐霞君';//徐霞君
        $notify=UID;
        // $email='brenda@daltonventure.cn';
        $uuid=uuid();
        $time=time();
        $mon=strtotime('this week Monday',$time);
        $sun=strtotime('next week Monday',$time)-1;

        $input_date=date("Y").'-W'.date('W');//周次
        $date=date('Y-m-d H:i:s',$time);//填表日期

        $name="道彤投资工作周报 ".$input_date." 期";//编号
        $e=load("m/entity_m")->get("and del=0 and `type`='financing'  and `input_date` >= '$mon' and `input_date` <= '$sun' ");
        $input_peoples=[];
        $new_companies=[];
        foreach($e as $item){
            $idata=_decode($item['data']);
            $input_people=$item['input_people'];
            $input_peoples[$input_people][]=$item;
            $ncompany=$item;
            $idata['_rel']=$uuid;
            $idata['_rel_view']='weekly_report';
            $idata['seg']='new_company';
            $idata['notify']=[$notify];
            $ncompany['data']=$idata;
            $ncompany['type']='new_company';
            unset($ncompany['uuid']);
            $new_companies[]=$ncompany;
        }
        $sll=$name;
        $sll_BP="";
        $allusr=load('m/usr_m')->db->query("select uuid,name from `usr`  ");
        foreach($allusr as $item){
            $uid=$item['uuid'];
            $all_usrs[$uid]=$item['name'];
        }
        foreach($input_peoples as $k=>$people){
            $nums=count($people);
            $pname=$all_usrs[$k];
            $users[$k]=$pname;
            $sll_BP.=",".$pname."共录入BP个数为".$nums;
        }
        if(empty($sll_BP))$sll_BP=",共录入BP个数为0";
        $sll.=$sll_BP;

        //项目 关联数据处理
        if($new_companies)load("m/entity_m")->bunch_add($new_companies);

        //使用时长 关联数据处理
        $this->use_time_length($uuid,$mon,$sun,$users);

        //周报 数据处理
        $elem['uuid']=$uuid;
        $edata['name']=$name;
        $edata['date']=$date;
        $edata['s11']=$sll;
        $edata['weeks']=$input_date;
        $edata['seg']='weekly_report';
        $edata['update_date']=$time;
        $edata['input_people_label']=$send_name;
        $edata['input_people']=UID;
        $elem['data']=$edata;
        $elem['name']=$elem['name_gbk']=$name;
        load("m/entity_m")->replace($elem,'weekly_report');
        $link='?/weekly_report/view/about/'.$uuid;


        //系统内消息通知
        $msg['content']=$send_name." 创建了 <span class='updatings-entity-name'>".$name."</span> 。";
        $msg['link']=$link;
        $msg['tmpl']='link';
        $msg['type']='weekly_report';
        load("m/msg_m")->add($notify,$msg,false);

    }

    /**
     * @author dy
     * task03297
     * 统计周报使用时长
     * people数据格式 uuid=>小明
     * eid是周报uuid
     */
    function use_time_length($eid,$start_time,$endtime,$people){
        if(!$eid)return;

        $active_time=load("m/trace_m")->count_active_time($start_time,$endtime);
        $start_day = date("d",$start_time);

        //整理数据
        foreach ($people as $k => $v) {
            $user[$k]['name'] = $v;
            $all = 0;
            for ($i=0; $i <7 ; $i++) {
                $day=$start_day+$i;
                $minute=$active_time[$k]['data'][$day];
                $user[$k][$i+1]=intval($minute);
                $all += intval($minute);
            }
            $user[$k]['all_time'] = $all;
        }

        //插入数据
        foreach ($user as $k => $v) {
          $elem = array();
          $elem['sid'] = WEBSID;
          $elem['type']='use_time';
          $elem['name']=$elem['name_gbk']=$elem['data']['name'] = $v['name'];
          $elem['data']['monday'] = $v['1'];
          $elem['data']['tuesday'] = $v['2'];
          $elem['data']['wednesday'] = $v['3'];
          $elem['data']['thursday'] = $v['4'];
          $elem['data']['friday'] = $v['5'];
          $elem['data']['saturday'] = $v['6'];
          $elem['data']['sunday'] = $v['7'];
          $elem['data']['summary'] = $v['all_time'];
          $elem['_rel']=$elem['data']['_rel'] = $eid;
          $elem['data']['_rel_view'] = "weekly_report";
          $elem['data']['seg'] = "use_time";
          $this->em->replace_bare($elem);
        }
      }



    //每天提醒员工登陆系统（周末、节假日除外）
    function notice(){
        $notice_config = load('m/config_m')->get(" and `key` = 'daltonnotice'");
        $reminder_content = _decode($notice_config[0]['data'])['reminder_content'];
        $url =WWW?: $_SERVER['HTTP_HOST'];//地址
        $sid = WEBSID;
        $url=$url.'/?/msg';
        $dates[] = date('Y-m-d');
        $r = curl_post("https://db.current.vc/?/api/holiday",_encode(["date"=>$dates]));
        $r = _decode($r);
        //是否为节假日
        $user_id = '';
        if($r['result'][$dates[0]] == 0){
            $user = load('m/space_usr_m')->get(" and sid = '$sid'");
            foreach($user as $user_v){

                $elem=array(
                    'type'=>'notice',
                    'content'=>$reminder_content,
                    'url_jump'=>false,
                    'is_read' => 0
                );
                load('m/msg_m')->add($user_v['uid'],$elem,'add',false);

                $user_id .= "'".$user_v['uid']."',";
            }

            $user_uuid = substr($user_id,0,-1);
            $user = load('m/usr_m')->get(" and uuid in ($user_uuid)");  //所有需要推送的
            $common = "公司全体员工：<br>
            &nbsp;&nbsp;&nbsp;&nbsp;为提高公司办公信息化水平和工作效率，实现公司管理的统一化、信息化、高效化。经过前期试运行，目前系统已能达到正式运行的条件，望各部门按照要求规范使用。";
            foreach($user as $user_v){
                $email_data = array(
                    'email' => $user_v['email'],
                    'subject' => $reminder_content,
                    'body' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                      <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                        <meta name="viewport" content="width=device-width"/>
                        <title>[道彤投资]</title>
                      </head>
                      <body style="margin: 0;padding: 0;background-color: #F5F5F5;height: 100% !important;width: 100% !important;">
                        <div style="max-width:600px;margin:40px auto;border:solid 1px #eee;box-shadow: 0px 0px 10px #212f472e;">
                          <div style="padding:0px;background-color:#fff;" >
                                  </div>
                                <div style="padding:0px 20px 20px 20px ;background-color:#fff;" >
                           <div style="font-family: Helvetica;font-size: 20px;line-height: 125%;border-top: 1px solid #E3E3E3;font-weight: 800;"><p>【道彤投资】登陆提醒</p></div>
                                   <div style="padding-left: 10px;"><p class="cont_hover" style="font-size: 16px;">'.$common.'</p></div>
                            <a href="'.$url.'" target="_blank" style="font-weight:bold;background-color:#3f7ea6;color: #FFFFFF;display: block;text-align:center;text-decoration: none;margin:40px auto;padding:10px;width:100px;font-size: 16px;">前往</a>
                          </div>
                          <div style="background-color:#f6f6f6;border:solid 1px #eee;padding:10px 20px 10px 20px;color: #404040;font-family: Helvetica;font-size: 16px;line-height: 125%;text-align: left;word-wrap: break-word;">
                            <div>
                              <p>系统邮件，请勿回复。</p>
                            </div>
                            <p>如无法打开，请复制以下完整地址至浏览器打开</p>
                            <p style="line-height: 125%;">'.$url.'</p>
                          </div>
                              </div>

                      </body>
                    </html>',
                );
                load('m/queue_m')->add_mail($email_data);//邮箱添加
            }
        }
    }

    /**
     * @author cy
     * task03507
     * 项目的财务报告季度更新
     */
  function financial_reports(){
    $m = load('m/entity_m');

    $financial_reports_config = load('m/config_m')->get(" and `key` = 'daltonnotice'");
    $financial_content = _decode($financial_reports_config[0]['data'])['financial_reports'];

    $month = ['4','7','10'];
    $dates_m = date('m');
    $dates_d = date('d');

    //$user_id = '';
    if((in_array($dates_m,$month) && $dates_d == '15') || ($dates_m == '1' && $dates_d == '30')){  //在每年4 7 10月的15日或每年1月30日
      $sid = WEBSID;
      $company = $m->get(" AND `type` = 'company' AND `sid` = '$sid' AND `data`->>'$.company_state' = '已投' AND `del` =0");
      $team_manager = [];
      foreach($company as $company_v){
        $company_data = _decode($company_v['data']);
        if(!in_array($company_data['team_manager'],$team_manager)){
          //$team_manager[] = $company_data['team_manager'];//所有头后管理项目负责人ID
          array_push($team_manager,$company_data['team_manager']);
        }
      }

      //$user_uuid = substr($user_id,0,-1);
      //if(empty($user_uuid)) return;
      //$user = load('m/usr_m')->get(" and uuid in ($user_uuid)");  //所有项目负责人
      // foreach($user as $user_v){
      //   $email_data = array(
      //     'email' => $user_v['email'],
      //     'subject' => $financial_content,
      //   );

      //   load('m/queue_m')->add_mail($email_data);//邮箱添加
      // }

      $elem=array(
        'type'=>'notice',
        'content'=>$financial_content,
        'url_jump'=>false
      );
      //dump($company,$team_manager);
       load('m/msg_m')->batch($team_manager,'add',$elem);
    }
  }

    /**
     * @description: financing模块更新状态为投后管理，更新项目状态为已投
     * @param: $eid [string] uuid
     * @param: $data [string] json_data
     * @author: gaoyiqiong <yqgao@pepm.com.cn>
     * @version: v5.5
     */
    function check_financing_step($eid)
    {
      $elem = $this->em->get_one($eid);
      $type = $elem['type'];
      if ($type != 'company' && $type !='financing') return false;    // 其他请求不做判断

      $data = _decode($elem['data']);

      // company模块 请求
      if ($type == 'company'){
        if ($data['state'] == '已投') return false;
        // 查找关联的financing数据
        $financing = $this->em->get(" AND `del`=0 AND `type`='financing' AND `data`->>'$.company'='$eid' AND `data`->>'$.state'='投后管理'");
        if(!$financing) return false;
        $company_uuid = $eid;
      }
      // financing模块 请求
      if ($type == 'financing'){
        if ($data['state'] != '投后管理') return false;
        $company_uuid = $data['company'];
      }

      $this->update_company_state($company_uuid, '已投');
    }

    /**
     * @description: 更新项目状态
     * @param: $uuid <string> uuid
     * @param: $state <string> 要更新的状态
     * @author: gaoyiqiong <yqgao@pepm.com.cn>
     * @version: v5.5
     */
    function update_company_state($uuid, $state)
    {
      $company = $this->em->get_one($uuid);
      $company_data = _decode($company['data']);
      $company_data['company_state'] = $state;
      $company_data['company_state_label'] = $state;
      // ↓ 申请访问人员清空 -------------------
      $company_data['applicant'] = '';
      $company_data['applicant_label'] = '';
      // ↑ 申请访问人员清空 -------------------
      $company['data'] = $company_data;

      $this->em->replace($company, $company['type'], 'update', '', false, false, false);
    }

    function render_status($param=[]){
        foreach ($param['data'] as $pk => &$pv) {

            $uuid = $pv['0'];
            $row = load('m/entity_m')->get_one($uuid);

            $data = _decode($row['data']);
            $company = $uuid;

            if (empty($company)) {
                continue;
            }
            $duties = load('entity_m')->get_by_index('duties','company',$company);

            foreach($duties as $dk => $dv){

                $dv_data = _decode($dv['data']);

                $state = $dv_data['state'];

                if ($state=='已完成') {
                    $pv[7] = '已完成';
                }else{
                    // 过期
                    if (strtotime($dv_data['dealline'])<time()) {
                        // $pv[7] = '逾期';
                    }else{
                        if ( $state=='待处理' || $state=='处理中' || $state=='暂停' ) {
                            $pv[7] = $state;
                        }
                    }
                }

            }

        }
        return $param;
    }

    function board_status($param = []){

      foreach ($param as $pk => &$pv) {
            $data = _decode($pv['data']);
            $company = $pv['uuid'];

            if (empty($company)) {
                continue;
            }
            $duties = load('entity_m')->get_by_index('duties','company',$company);

            foreach($duties as $dk => $dv){

                $dv_data = _decode($dv['data']);

                $state = $dv_data['state'];

                if ($state=='已完成') {
                    // 绿色
                    $pv['_board_item_bg'] = 'background:rgba(80,171,53,0.5);color:#FFF;';
                }else{
                    // 过期
                    if (strtotime($dv_data['dealline'])<time()) {
                        // 红色
                        $pv['_board_item_bg'] = 'background:rgba(215,67,50,0.5);color:#FFF;';
                    }else{
                        if ( $state=='待处理' || $state=='处理中' || $state=='暂停' ) {
                            $pv['_board_item_bg'] = 'background:rgba(222,193,67,0.5);color:#FFF;';
                        }
                    }
                }
            }
        }

        return $param;
    }


    //交易管理（financing）模块bp阶段，当提醒日期（reminderdate）有值时，按照所填日期早上10点发送通知给该交易的管理的负责人（team_manager）提醒跟进。提醒内容为：xx项目BP跟进提醒！
    function financing_msg($sid){
      $lists = $this->em->get(" and `del`=0 and `sid`='$sid' and `type`='financing' ");

      foreach ($lists as $k => $v) {
        $v['data'] = _decode($v['data']);
        if($v['data']['reminderdate']){

          $notify_to = [$v['data']['team_manager']];

          $data['eid'] = $v['uuid'];
          $data['link']="?/".$v['type']."/view/about/".$v['uuid'];
          $data['tmpl']='link';
          $data['type']=$v['type'];
          $data['send_time']=time();
          $data['sid']=$sid;
          $data['content']= $v['data']['name']."项目BP跟进提醒！";

          load('m/msg_m')
            ->set_msg_vendor(['msg'])
            ->set_force_mode(true)
            ->batch($notify_to, "add", $data, true);
        }
      }
    }

    //每个季度(2.30、5.30、8.30、11.30早上10点）创建一个待办任务，任务名称为提醒xx负责人看下bp库，半年（6.30、10、30早上10点）创建一个待办任务，任务名称为提醒xx负责人研究放            弃项目。模块的状态为待处理，责任人（member）为投资总监、投资副总裁这些人。开始时间为提醒日期，deadline为1个月后，发布事件为生成该任务的时间
    function quarter_bp($sid,$type=''){

      $users = load("m/space_usr_m")->get_all_users($sid,true);

      $year = date('Y',time());
      $quarter = intval(ceil((date('n'))/3));

      foreach ($users as $k => $v) {
        $v['data'] = _decode($v['data']);
        $data = [];
        if(in_array('投资总监', $v['data']['role_label'])||in_array('投资副总裁', $v['data']['role_label'])){
          $data['name'] = '查看'.$year.'年'.$quarter.'季度bp库';
          if($type=='semiyearly'){
            $data['name'] = '研究'.$year.'年'.($quarter>2?'下半年':'上半年').'放弃项目';
          }

          $data['state'] = '待处理';
          $data['state_label'] = '待处理';
          $data['date'] = date('Y-m-d',time());
          $data['creat_date'] = date('Y-m-d H:i:s',time());
          $data['dealline'] = date('Y-m-d',strtotime('+1 month'));
          $data['importance'] = '次要不急';
          $data['importance_label'] = '次要不急';
          $data['member'] = $v['uid'];
          $data['member_label'] = $v['data']['name'];

          $elem['sid'] = $sid;
          $elem['type'] = 'duties';
          $elem['data'] = $data;

          $this->em->replace($elem,'duties','add',false,false,false,false);
        }
      }
    }

    //每年4月、7月、10月的15日，1月的30日的早上10点创建一条任务管理，提醒内容为财务报告是需要季度更新，请相关负责人及时跟进。模块的状态为待处理，责任人（member）为殷丹，开始时间为4月、7月、10月的15日、1月的30日，deadline为1个月后，发布事件为生成该任务的时间
    function financial($sid){

      $users = load("m/space_usr_m")->get_all_users($sid,true);

      $year = date('Y',time());
      $quarter = intval(ceil((date('n'))/3));

      foreach ($users as $k => $v) {
        $v['data'] = _decode($v['data']);
        $data = [];
        if(in_array('财务负责人', $v['data']['role_label'])){
          $data['name'] = $year.'年'.$quarter.'季度财务报告是需要季度更新';
          $data['state'] = '待处理';
          $data['state_label'] = '待处理';
          $data['date'] = date('Y-m-d',time());
          $data['creat_date'] = date('Y-m-d H:i:s',time());
          $data['dealline'] = date('Y-m-d',strtotime('+1 month'));
          $data['importance'] = '重要紧急';
          $data['importance_label'] = '重要紧急';
          $data['member'] = $v['uid'];
          $data['member_label'] = $v['data']['name'];

          $elem['sid'] = $sid;
          $elem['type'] = 'duties';
          $elem['data'] = $data;

          $this->em->replace($elem,'duties','add',false,false,false,false);
        }
      }
    }

    //交易管理financing）模块，状态（state）是项目立项、尽调调查、项目投决、投资交割、投后管理、退出交割的话，按照更新时间（gxtime）后每个季度创建一条待办任务，提醒内容为：请及时实地考察并填写考察反馈结果。模块的状态为待处理，责任人（member）为该交易的负责人（team_manager）。开始时间为提醒日期，deadline为1个月后，发布事件为生成该任务的时间
    function financing_report($sid){
      $time = strtotime(date('Y-m-d',strtotime("-3 month")));
      $endtime = $time+86400;

      $lists = $this->em->get(" and `del`=0 and `type`='financing' and `sid`='$sid' and `data`->>'$.state' in ('项目立项','尽职调查','项目投决','投资交割','投后管理','退出交割') and UNIX_TIMESTAMP(`data`->>'$.gxtime')>='$time' and  UNIX_TIMESTAMP(`data`->>'$.gxtime')<'$endtime' ");

      foreach ($lists as $k => $v) {
        $v['data'] = _decode($v['data']);

        $data['name'] = '实地考察'.$v['data']['name'].'项目，并反馈结果';
        $data['state'] = '待处理';
        $data['state_label'] = '待处理';
        $data['date'] = date('Y-m-d',time());
        $data['creat_date'] = date('Y-m-d H:i:s',time());
        $data['dealline'] = date('Y-m-d',strtotime('+1 month'));
        $data['importance'] = '重要紧急';
        $data['importance_label'] = '重要紧急';
        $data['member'] = $v['data']['team_manager'];
        $data['member_label'] = $v['data']['team_manager_label'];

        $elem['sid'] = $sid;
        $elem['type'] = 'duties';
        $elem['data'] = $data;

        $this->em->replace($elem,'duties','add',false,false,false,false);
      }
    }

    function meeting_auto($sid)
    {
      $start_time = strtotime(date('Y-m-01', strtotime('-1 month')));
      $end_time = strtotime(date('Y-m-t', strtotime('-1 month')));

      $year = date('Y', strtotime('-1 month'));
      $month = date('m', strtotime('-1 month'));

      //默认取当前上月 测试的时候解开下面的语句
      //$start_time = strtotime('2021-06-01');
      //$end_time = strtotime('2021-06-30');

      //$year = '2021';
      //$month = '06';

      $com_list = $this->em->get(" AND `del`=0 AND `sid`='$sid' AND `type`='company' AND UNIX_TIMESTAMP(`data`->>'$.date')>='$start_time' AND UNIX_TIMESTAMP(`data`->>'$.date')<'$end_time'  ");

      $meet_list = $this->em->get(" AND `del`=0 AND `sid`='$sid' AND `type`='company_meeting' AND UNIX_TIMESTAMP(`data`->>'$.creationdate')>='$start_time' AND UNIX_TIMESTAMP(`data`->>'$.creationdate')<'$end_time'  ");

      $com = [];
      foreach ($com_list as $k => $v) {
        $v['data'] = _decode($v['data']);

        $user = $v['data']['team_manager'];

        $com[$user] += 1;
      }

      $meet = [];
      foreach ($meet_list as $k => $v) {
        $v['data'] = _decode($v['data']);

        $user = explode(',', $v['data']['team_meeting']);
        foreach ($user as $k1 => $v1) {
          $meet[$v1] += 1;
        }
      }

      $users = load("m/space_usr_m")->get_all_users($sid);

      foreach ($users as $k => $v) {
        $user = $v['uid'];

        $elem = $this->em->get(" AND `del`=0 AND `sid`='$sid' AND `type`='meetingsquantum' AND `data`->>'$.username'='$user' AND `data`->>'$.year'='$year' AND `data`->>'$.month'='$month' ")[0];

        if($com[$user]||$meet[$user]){
          if($elem)$elem['data'] = _decode($elem['data']);
          $elem['data']['username'] = $user;
          $elem['data']['username_label'] = vusr($user)['name'];
          $elem['data']['year'] = $year;
          $elem['data']['month'] = $month;
          $elem['data']['newcompany'] = $com[$user];
          $elem['data']['newmetting'] = $meet[$user];


          $elem['type'] = 'meetingsquantum';

          $this->em->replace_bare($elem);
        }
      }

      $this->em->db->clear_cache();
    }

    function meeting_auto_for_year($sid){
      $sid = WEBSID;

      $lists = $this->em->get(" and `del`=0 and `sid`='$sid' and `type`='meetingsquantum' ");

      $data = [];
      foreach ($lists as $k => $v) {
        $v['data'] = _decode($v['data']);
        $data[$v['data']['year']][$v['data']['username']][intval($v['data']['month'])]['newcompany'] = $v['data']['newcompany'];
        $data[$v['data']['year']][$v['data']['username']][intval($v['data']['month'])]['newmetting'] = $v['data']['newmetting'];
      }

      $year_list = [];
      //计算同比环比
      foreach ($data as $year => $v) {
        foreach ($v as $name => $list) {
          $last_year = $year-1;
          krsort($list);
          $new_month = key($list);
          $last_month = $new_month-1;
          //1月的情况
          if($last_month==0){
            $last_month = 1;
          }

          $this_yaer_com = $data[$year][$name][$new_month]['newcompany'];//今年本月项目数量
          $last_yaer_com = $data[$last_year][$name][$new_month]['newcompany'];//去年同月项目数量
          $last_month_com = $data[$year][$name][$last_month]['newcompany'];//今年上月项目数量

          $this_yaer_met = $data[$year][$name][$new_month]['newmetting'];//今年本月会议数量
          $last_yaer_met = $data[$last_year][$name][$new_month]['newmetting'];//去年同月会议数量
          $last_month_met = $data[$year][$name][$last_month]['newmetting'];//今年上月会议数量

          if($last_yaer_com&&$last_yaer_com!=0){
            $year_list[$year][$name]['tb_num'] = $this_yaer_com-$last_yaer_com;
            $year_list[$year][$name]['tb_bili'] = (($this_yaer_com-$last_yaer_com)/$last_yaer_com)*100;
          }else{
            $year_list[$year][$name]['tb_num'] = "/";
            $year_list[$year][$name]['tb_bili'] = 0;
          }

          if($last_yaer_met&&$last_yaer_met!=0){
            $year_list[$year][$name]['tb_num_mt'] = $this_yaer_met-$last_yaer_met;
            $year_list[$year][$name]['tb_bili_mt'] = (($this_yaer_met-$last_yaer_met)/$last_yaer_met)*100;
          }else{
            $year_list[$year][$name]['tb_num_mt'] = "/";
            $year_list[$year][$name]['tb_bili_mt'] = 0;
          }

          if($last_month_com&&$last_month_com!=0){
            $year_list[$year][$name]['hb_num'] = $this_yaer_com-$last_month_com;
            $year_list[$year][$name]['hb_bili'] = (($this_yaer_com-$last_month_com)/$last_month_com)*100;
          }else{
            $year_list[$year][$name]['hb_num'] = "/";
            $year_list[$year][$name]['hb_bili'] = 0;
          }

          if($last_month_met&&$last_month_met!=0){
            $year_list[$year][$name]['hb_num_mt'] = $this_yaer_met-$last_month_met;
            $year_list[$year][$name]['hb_bili_mt'] = (($this_yaer_met-$last_month_met)/$last_month_met)*100;
          }else{
            $year_list[$year][$name]['tb_num_mt'] = "/";
            $year_list[$year][$name]['tb_bili_mt'] = 0;
          }
        }
      }

      foreach ($year_list as $year => $v) {
        foreach ($v as $user => $lists) {
          $elem = $this->em->get(" AND `del`=0 AND `sid`='$sid' AND `type`='meeting_year' AND `data`->>'$.year'='$year' AND `data`->>'$.user'='$user' ")[0];

          if(!$elem){
            $elem['type'] = 'meeting_year';
            $elem['data']['year'] = $year;
            $elem['data']['user'] = $user;
            $elem['data']['user_label'] = vusr($user)['name'];
          }else{
            $elem['data'] = _decode($elem['data']);
          }

          $elem['data']['tb_num'] = $lists['tb_num'];
          $elem['data']['tb_bili'] = $lists['tb_bili'];
          $elem['data']['hb_num'] = $lists['hb_num'];
          $elem['data']['hb_bili'] = $lists['hb_bili'];

          $elem['data']['tb_num_mt'] = $lists['tb_num_mt'];
          $elem['data']['tb_bili_mt'] = $lists['tb_bili_mt'];
          $elem['data']['hb_num_mt'] = $lists['hb_num_mt'];
          $elem['data']['hb_bili_mt'] = $lists['hb_bili_mt'];

          $this->em->replace_bare($elem);
        }
      }

      $this->em->db->clear_cache();
    }

    /**
     * task/2021-05256
     * 同步数据到dataroom
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.0
     */
    public function sync2DataRoom($sid)
    {
      if (!$sid) $sid = WEBSID;
      $this->currentSid = $sid;
      switch (WEBSID) {
          case 'gkas42xvt2': $this->mainSid = 'ggi7uujrxq';break;     // 正式系统
          case 'h467nfeehp': $this->mainSid = 'h0bjkaq7fr';break;     /** 本地 @todo del */
          case 'h2tv16gnyd': $this->mainSid = 'h2tv0gglge';break;     /** dev @todo del */
      }

      if(!$this->mainSid) {
        echo 'sid不正确，请检查。';
        return false;
      }

      $this->doSyncByList([
        'lpcompany' => [
          'type' => 'company',
          'sid' => $this->mainSid,
          'data' => [
            'company_state' => '已投',
          ],
        ],
        'company_post_filling' => [
          'type' => 'company_post_filling',
          'sid' => $this->mainSid
        ],
      ]);
    }

    /**
     * task/2021-05256
     * 同步相应规则的数据
     * @param array $list
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.0
     */
    private function doSyncByList($list)
    {
      foreach ($list as $mod => $filter) {
        $sql = "SELECT * FROM `entity` WHERE 1 ";
        foreach ($filter as $field => $value) {
          // data字段
          if ($field == 'data') {
            foreach ($value as $dataField => $v) {
              $sql .= " AND `$field`->>'$.$dataField' = '$v'";
              continue;
            }
            continue;
          }
          if (is_array($value)) {
            // 待补充规则
            continue;
          }

          // 键值对
          $sql .= " AND `$field` = '$value'";
        }

        // 同步符合条件的数据
        $data = $this->em->db->query($sql);
        foreach ($data as $elem) {
          $uuid = $elem['uuid'];
          $sid = $filter['sid'];
          $fm = load('m/file_m');
          $sql = "SELECT * FROM `file` WHERE 1 AND `sid` = '$sid' AND `eid` = '$uuid'";
          $files = $fm->db->query($sql);
          // 同步文档
          foreach ($files as $file)
          {
            $syncFile = $fm->get_one($file['uuid']);
            $file['data'] = _decode($file['data']);
            $file['sid'] = $this->currentSid;
            $file['doc_type'] = $mod;
            // 有则更新无则新增
            if ($syncFile) {
              $fm->update($file['uuid'], $file);
            } else {
              $fm->add($file);
            }
          }
          // 同步模块数据
          $syncElem = $this->em->get_one($uuid);

          $elem['sid'] = $this->currentSid;
          $elem['data'] = _decode($elem['data']);
          $elem['type'] = $mod;

          if ($syncElem) {
            $this->em->replace_bare($elem);
            continue;
          }

          $this->em->add($elem, $mod);
        }
      }
    }


    function project_priv($uid){
      // return;
      // echo "ddd";die;
      $em  = load("m/entity_m");
      $sum = load("space_usr_m");
      $cm  = load("config_m");
      // $hm = load("history_m");
      // $hm->get(" AND `extra`='space_usr' AND  ");
      $sid = WEBSID;
      $su = $sum->get(" AND `sid`='$sid' AND `uid`='$uid'")[0];
      $su['data'] = _decode($su['data']);
      $uname = $su['data']['name'];

      $projects = $su['data']['viewableproject'];
      $projects_label = $su['data']['viewableproject_label'];
      $stars = $su['data']['starproject'];
      $stars_label = $su['data']['starproject_label'];

      $a_arr = explode(',',$projects);
      $b_arr = explode(',',$stars);

      $c_elems1 = $em->get(" AND `del`=0 AND `data`->>'$.viewableproject' like '%$uid%'");
      $c_elems2 = $em->get(" AND `del`=0 AND `data`->>'$.starproject' like '%$uid%'");

      $c_eids1 = array_column($c_elems1,'uuid');
      $c_eids2 = array_column($c_elems2,'uuid');

      $need_del1 = [];
      $need_del2 = [];

      foreach($c_eids1 as $c_eid1){
        if(!in_array($c_eid1,$a_arr)){
          $need_del1[] = $c_eid1;
        }
      }

      foreach($c_eids2 as $c_eid2){
        if(!in_array($c_eid2,$b_arr)){
          $need_del2[] = $c_eid1;
        }
      }
      if($need_del1){
        foreach($need_del1 as $del1){
          $elem = $em->get($del1);
          $elem['data'] = _decode($elem['data']);
          $aindex = array_search($uid,explode(",",$elem['data']['viewableproject']));
          $oarray1 = explode(",",$elem['data']['viewableproject']);
          $oarray2 = explode(",",$elem['data']['viewableproject_label']);
          array_splice($oarray1,$aindex,1);
          array_splice($oarray2,$aindex,1);
          $elem['data']['viewableproject'] = join(",",$oarray1)?:",";
          $elem['data']['viewableproject_label'] = join(",",$oarray2)?:",";
          $em->replace_bare($elem);
          if($elem) { unset($elem); }
        }
      }

      if($need_del2){
        foreach($need_del2 as $del2){
          $elem = $em->get($del2);
          $elem['data'] = _decode($elem['data']);
          $aindex = array_search($uid,explode(",",$elem['data']['starproject']));
          $oarray1 = explode(",",$elem['data']['starproject']);
          $oarray2 = explode(",",$elem['data']['starproject_label']);
          array_splice($oarray1,$aindex,1);
          array_splice($oarray2,$aindex,1);
          $elem['data']['starproject'] = join(",",$oarray1)?:",";
          $elem['data']['starproject_label'] = join(",",$oarray2)?:",";
          $em->replace_bare($elem);
          if($elem) { unset($elem); }
        }
      }


      $project_elems = $em->get(" AND `del`=0 AND `uuid` in ('".join("','",$a_arr)."') AND (`data`->>'$.viewableproject' NOT LIKE '%$uid%' OR NOT json_contains_path(`data`,'one','$.viewableproject'))");
      $star_elems = $em->get(" AND `del`=0 AND `uuid` in ('".join("','",$b_arr)."') AND (`data`->>'$.starproject' NOT LIKE '%$uid%' OR NOT json_contains_path(`data`,'one','$.starproject'))");

      foreach($project_elems as $elem) {
        $elem['data'] = _decode($elem['data']);
        $elem['data']['viewableproject'] = join(",",array_filter(array_merge(explode(",",$elem['data']['viewableproject']),[$uid])));
        $elem['data']['viewableproject_label'] = join(",",array_filter(array_merge(explode(",",$elem['data']['viewableproject_label']),[$uname])));
        $em->replace_bare($elem);
      }
      foreach($star_elems as $elem) {
        $elem['data'] = _decode($elem['data']);
        $elem['data']['starproject'] = join(",",array_filter(array_merge(explode(",",$elem['data']['starproject']),[$uid])));
        $elem['data']['starproject_label'] = join(",",array_filter(array_merge(explode(",",$elem['data']['starproject_label']),[$uname])));
        $em->replace_bare($elem);
      }


      //doc priv trigger
      $viewablefile       = $su['data']['viewablefile'];
      $viewablefile_label = $su['data']['viewablefile_label'];

      $viewablefile_arr = explode(",",$viewablefile);
      $doc = $cm->key("doc_lpcompany",true);
      $items = $doc['data']['item'];
      $update = false;
      foreach($items as $key=>$item){
        $doc_priv = $item['privilege']['list'];
        $doc_priv_arr = explode(",",$doc_priv);
        if(in_array($key,$viewablefile_arr)){
          if(!in_array($uid,$doc_priv_arr)){
            $doc_priv_arr = array_merge($doc_priv_arr,[$uid]);
            $update = true;
          }
        }else{
          if(in_array($uid,$doc_priv_arr)){
            $temp_arr = $doc_priv_arr;
            $aindex = array_search($uid,$temp_arr);
            array_splice($temp_arr,$aindex,1);
            $doc_priv_arr = $temp_arr;
            $update = true;
          }
        }
        $doc_priv = join(",",array_filter(array_values($doc_priv_arr)));
        $items[$key]['privilege']['list'] = $doc_priv;
      }
      if($update){
        $doc['data']['item'] = $items;
        $cm->update($doc['uuid'],$doc);
      }

      $em->db->clear_cache();
      load("c/config")->rebuild_priv("gtdz0uxpp8",1);

    }

    /**
     * task/2021-06773 交易上传工商执照后修改任务为已完成
     * @param string $tradeUUID 交易 uuid
     * @author stone <yqgao@pepm.com.cn>
     * @version v5.5
     */
    public function updateTaskStateByUpload($tradeUUID, $tradeData)
    {
      // 检查是否上传工商执照
      $tradeData = _decode($tradeData);
      if (!$tradeData['gef55hw6hc'][1]) return false;

      // 查找任务
      $duties = $this->em->get("AND `del` = 0 AND `type` = 'duties' AND `data`->>'$.financing' = '$tradeUUID' AND `data`->>'$.type' = '1' AND `data`->>'$.state' <> '已完成'")[0];
      if (!$duties) return false;

      // 修改状态为已完成
      $dutiesData = _decode($duties['data']);
      $dutiesData['state'] = $dutiesData['state_label'] = '已完成';
      $duties['data'] = $dutiesData;

      $this->em->replace_bare($duties);
    }

    function view_ytxm_cal($eid){
    $sid = WEBSID;
    $elem = $this->em->get($eid);
    $elem['data'] = _decode($elem['data']);

    $first_deal = $this->em->get(" and `sid`='$sid' and `del`=0 and `type`='financing' and `data`->>'$.company'='$eid' order by UNIX_TIMESTAMP(`data`->>'$.ic_date') ",0,1)[0];

    $elem['data']['fund'] = '';
    $elem['data']['fund_label'] = '';
    $elem['data']['time'] = '';
    $elem['data']['cs_round'] = '';
    $elem['data']['cs_round_label'] = '';
    $elem['data']['initial_cost'] = '';
    $elem['data']['initial_cost_label'] = '';
    $elem['data']['initial_proportion'] = '';
    $elem['data']['initial_proportion_label'] = '';
    $elem['data']['initial_valuation'] = '';
    $elem['data']['initial_valuation_label'] = '';
    $elem['data']['mil_value'] = '';
    $elem['data']['mil_value_label'] = '';

    if($first_deal){
      $first_deal['data'] = _decode($first_deal['data']);

      $elem['data']['fund'] = $first_deal['data']['fund'];
      $elem['data']['fund_label'] = $first_deal['data']['fund_label'];
      $elem['data']['time'] = $first_deal['data']['ic_date'];
      $elem['data']['cs_round'] = $first_deal['data']['round'];
      $elem['data']['cs_round_label'] = $first_deal['data']['round_label'];
      $elem['data']['initial_cost'] = $first_deal['data']['actual_amount'];
      $elem['data']['initial_cost_label'] = $first_deal['data']['actual_amount_label'];
      $elem['data']['initial_proportion'] = $first_deal['data']['inv_ratio'];
      $elem['data']['initial_proportion_label'] = $first_deal['data']['inv_ratio_label'];
      $elem['data']['initial_valuation'] = $first_deal['data']['fin_current_value'];
      $elem['data']['initial_valuation_label'] = $first_deal['data']['fin_current_value_label'];
      $elem['data']['mil_value'] = $first_deal['data']['actual_post_value'];
      $elem['data']['mil_value_label'] = $first_deal['data']['actual_post_value_label'];
    }
    // 最近一条交易信息
    $end_deal = $this->em->get(" and `sid`='$sid' and `del`=0 and `type`='financing' and `data`->>'$.company'='$eid' order by UNIX_TIMESTAMP(`data`->>'$.ic_date') desc ",0,1)[0];
    // 累计投资成本
    $total_cost = $this->em->db->query("SELECT ifnull( SUM(`data`->>'$.actual_amount') , 0 ) as amount FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'financing' and `data`->>'$.company' =  '$eid'")[0]['amount'];

    $fund = '';
    $fund_label = '';
    // 参投基金
    $fund_data = $this->em->db->query("SELECT `data`->>'$.fund' as  fund,`data`->>'$.fund_label'as fund_label FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'financing' and `data`->>'$.company' =  '$eid'");

    if($fund_data){
      foreach($fund_data as $k=>$v){
        if(!$v['fund'] || !$v['fund'] ) continue;
        $fund= $fund.','.$v['fund'];
        $fund_label=$fund_label.'，'.$v['fund_label'];
      }

    }

    // 参投轮次
    $round_data = $this->em->db->query("SELECT GROUP_CONCAT(`data`->>'$.round' SEPARATOR ', ') as  round,GROUP_CONCAT(`data`->>'$.round_label' SEPARATOR ', ') as round_label FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'financing' and `data`->>'$.company' =  '$eid'")[0];
  
    $elem['data']['inv_date'] = '';
    $elem['data']['inv_ratio'] = '';
    $elem['data']['inv_ratio_label'] = '';
    $elem['data']['registered_capital'] = '';
    $elem['data']['fin_current_value'] = '';
    $elem['data']['inv_capital'] = '';
    $elem['data']['total_cost'] = '';

    // 未参与轮次
    $not_round_data = $this->em->db->query("SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'followround' and `data`->>'$.company' =  '$eid' order by input_date desc")[0];

    if($end_deal){
      $end_deal['data'] = _decode($end_deal['data']);
      // 如果最新一条未参与轮次的数据早于最新一条投资时间的数据，取未参与轮次的数据
      if($not_round_data && $not_round_data['input_date'] > strtotime($end_deal['data']['ic_date'])){
        $not_round_data['data'] = _decode($not_round_data['data']);
        $elem['data']['inv_ratio'] = $not_round_data['data']['gx7nat2624'];
        $elem['data']['inv_ratio_label'] = $not_round_data['data']['gx7nat2624_label'];
      }else{
        $elem['data']['inv_ratio'] = $end_deal['data']['total_ratio'];
        $elem['data']['inv_ratio_label'] = $end_deal['data']['total_ratio_label'];
      }
      
      $elem['data']['inv_date'] = $end_deal['data']['ic_date'];
      $elem['data']['registered_capital'] = $end_deal['data']['gain_capital'];
      $elem['data']['fin_current_value'] = $end_deal['data']['actual_post_value']; 
      $elem['data']['inv_capital'] = $end_deal['data']['inv_capital'];
      $elem['data']['total_cost'] = $total_cost;
      $elem['data']['fund'] = implode(',',array_unique(explode(',',$fund)));
      $elem['data']['fund_label'] = implode('，',array_unique(explode('，',$fund_label)));
      $elem['data']['rp_round'] = $round_data['round'];
      $elem['data']['rp_round_label'] = $round_data['round_label'];
    }

    $company_post_filling = $this->em->get(" and `sid`='$sid' and `del`=0 and `type`='company_post_filling' and `data`->>'$.company'='$eid' order by `input_date` desc ",0,1)[0];

    $elem['data']['gntsn8kkwp'] = '';
    $elem['data']['gntsn8kkwp_label'] = '';
    $elem['data']['gntsn9pkpp'] = '';
    $elem['data']['gntsn9pkpp_label'] = '';
    $elem['data']['file'] = '';
    $elem['data']['file_label'] = '';

    if($company_post_filling){
      $company_post_filling['data'] = _decode($company_post_filling['data']);

      $elem['data']['gntsn8kkwp'] = $company_post_filling['data']['gntsn8kkwp'];
      $elem['data']['gntsn8kkwp_label'] = $company_post_filling['data']['gntsn8kkwp_label'];
      $elem['data']['gntsn9pkpp'] = $company_post_filling['data']['gntsn9pkpp'];
      $elem['data']['gntsn9pkpp_label'] = $company_post_filling['data']['gntsn9pkpp_label'];
      $elem['data']['file'] = $company_post_filling['data']['file'];
      $elem['data']['file_label'] = $company_post_filling['data']['file_label'];
    }
    $this->em->replace_bare($elem);
  }

    function all_view_ytxm_cal($sid){
      $lists = $this->em->get(" and `del`=0 and `type`='company' and `sid`='$sid' and `data`->>'$.company_state'='已投' ",0,9999);

      foreach ($lists as $k => $v) {
        $this->view_ytxm_cal($v['uuid']);
      }
    }

  // 项目拟交接流程时更新临时项目负责人和项目小组字段
  function update_company_team($flow,$data){
    
    $entity_m = load('m/entity_m');

    $company_uuid = $data['form_data']['company']; // 项目uuid
    $newteam_manager = $data['form_data']['newteam_manager']; // 新负责人
    $newteam_manager_label = $data['form_data']['newteam_manager_label'];
    $new_group = $data['form_data']['newg0u9g7vlie']; //新小组
    $new_group_label = $data['form_data']['newg0u9g7vlie_label'];

    $company = $entity_m->get_one($company_uuid); 
    $company_data['team_manager_linshi'] = $newteam_manager;
    $company_data['team_manager_linshi_label'] = $newteam_manager_label;
    $company_data['group_linshi'] = $new_group;
    $company_data['group_linshi_label'] = $new_group_label;
    $company['data'] = $company_data;

    $entity_m->replace($company, 'company', 'update', false, false, false, false);
    
    $financing = $entity_m->get("and type='financing' and data->>'$.company'='$company_uuid' and del=0", 1, 9999);  //获取所有交易模块数据
    for($i = 0 ; $i < count($financing) ; $i++){
      $financing_data_rel[$i] = $entity_m->get_one($financing[$i]['uuid']); //获取项目交易数据的uuid取出数据rel
      $financing_data[$i] = _decode($financing_data[$i]['data']); 
    }
    for($i = 0 ; $i < count($financing_data) ; $i++){
      $financing_data[$i]['team_manager_linshi'] = $newteam_manager;
      $financing_data[$i]['team_manager_linshi_label'] = $newteam_manager_label;
      $financing_data[$i]['group_linshi'] = $new_group;
      $financing_data[$i]['group_linshi_label'] = $new_group_label;   
      $financing_data_rel[$i]['data'] = $financing_data[$i];
      $entity_m->replace($financing_data_rel[$i], 'financing', 'update', false, false, false, false);
   
    }
  }
  // 项目正式交接流程时更新项目负责人和项目小组字段
  function update_company_member($flow,$data){

    $entity_m = load('m/entity_m');

    $company_uuid = $data['form_data']['company']; // 项目uuid
    $newteam_manager = $data['form_data']['newteam_manager']; // 新负责人
    $newteam_manager_label = $data['form_data']['newteam_manager_label'];
    $new_group = $data['form_data']['newg0u9g7vlie']; //新小组
    $new_group_label = $data['form_data']['newg0u9g7vlie_label'];

    $company = $entity_m->get_one($company_uuid); 
    $company_data['team_manager'] = $newteam_manager;
    $company_data['team_manager_label'] = $newteam_manager_label;
    $company_data['g0u9g7vlie'] = $new_group;
    $company_data['g0u9g7vlie_label'] = $new_group_label;
    $company_data['team_manager_linshi'] = '';
    $company_data['team_manager_linshi_label'] = '';
    $company_data['group_linshi'] = '';
    $company_data['group_linshi_label'] = '';
    $company['data'] = $company_data;

    $entity_m->replace($company, 'company', 'update', false, false, false, false);

    $financing = $entity_m->get("and type='financing' and data->>'$.company'='$company_uuid' and del=0", 1, 9999);  //获取所有交易模块数据
    for($i = 0 ; $i < count($financing) ; $i++){
      $financing_data_rel[$i] = $entity_m->get_one($financing[$i]['uuid']); //获取项目交易数据的uuid取出数据rel
      $financing_data[$i] = _decode($financing_data[$i]['data']); 
    }
    for($i = 0 ; $i < count($financing_data) ; $i++){
      $financing_data[$i]['team_manager'] = $newteam_manager;
      $financing_data[$i]['team_manager_label'] = $newteam_manager_label;
      $financing_data[$i]['g0u9g7vlie'] = $new_group;
      $financing_data[$i]['g0u9g7vlie_label'] = $new_group_label;   
      $financing_data[$i]['team_manager_linshi'] = '';
      $financing_data[$i]['team_manager_linshi_label'] = '';
      $financing_data[$i]['group_linshi'] = '';
      $financing_data[$i]['group_linshi_label'] = '';  
      $financing_data_rel[$i]['data'] = $financing_data[$i];
      $entity_m->replace($financing_data_rel[$i], 'financing', 'update', false, false, false, false);

    }
  }

    /**
   * 外部填报“催办”逻辑
   *
   * @task    2022-04276 https://oa.vc800.com/?/task/view/about/h22alqgkfe
   * @param   string    $value [description]
   * @author  陈翔 <@pepm.com.cn>
   * @version release/v5.5
   */
  function resend_email($sid,$eid){
    if(!$eid){
      return;
    }

    $qm = load("m/queue_m");

    $sql = "SELECT * FROM `queue` WHERE `type`='email' AND `sid`='$sid' AND `related_type`='pm' AND `eid`='$eid' ORDER BY `send_time` DESC LIMIT 0,1";

    $email = $qm->db->query($sql)[0];

    if($email){
      $sql = "UPDATE `queue` SET `status`='pending' WHERE `id`='".$email['id']."'";
      $qm->db->query($sql);
    }
  }

  // 缴款通知书发送成功与否的抓取
  function check_succeeded($result){
    $msg_m = load('m/msg_m');
    $queue_m = load('m/queue_m');
    $entity_m = load('m/entity_m');
    $space_usr_m = load('m/space_usr_m');

    $param = $queue_m->get($result['mail_uuid']);
    $param['info'] = _decode($param['info']);

    $man = array_unique(array_merge($space_usr_m->get_by_role('role_ggcczib9k4','uid'),$space_usr_m->get_by_role('Admin','uid')));
    if($result['status'] == 'fail'){
      $msg = "系统提醒:【" . $param['info']['subject'] . "】" . "自动生成催款通知书邮件发送失败，请留意！";
      $elem = array(
        'eid' => uuid(),
        'type' => 'dept_email',
        'send_time' => time(),
        'content' => $msg,
      );
      for($i = 0; $i < count($man); $i++){
        $uuid = $msg_m->add($man[$i], $elem);
        $msg_m->adddb($uuid);
      }
    }elseif($result['status'] == 'success'){
      $msg = "系统提醒:【" . $param['info']['subject'] . "】" . "自动生成催款通知书邮件发送成功！";
      $elem = array(
        'eid' => uuid(),
        'type' => 'dept_email',
        'send_time' => time(),
        'content' => $msg,
      );
      for($i = 0; $i < count($man); $i++){
        $uuid = $msg_m->add($man[$i], $elem);
        $msg_m->adddb($uuid);
      }
    }
  }

  // 邀请用户邮件发送成功与否的抓取
  function usr_callback_mail($result){
    $msg_m = load('m/msg_m');
    $queue_m = load('m/queue_m');
    $entity_m = load('m/entity_m');
    $space_usr_m = load('m/space_usr_m');

    $param = $queue_m->get($result['mail_uuid']);
    $param['info'] = _decode($param['info']);

    $man = array_unique(array_merge($space_usr_m->get_by_role('role_h8864ulr1v','uid'),$space_usr_m->get_by_role('role_gdc3hk9988','uid')));
    if($result['status'] == 'fail'){
      $msg = "新增【" . $param['info']['person_invite'] . "】账号邀请邮件" . "发送失败，请留意";
      $elem = array(
        'eid' => uuid(),
        'type' => 'dept_email',
        'send_time' => time(),
        'content' => $msg,
      );
      for($i = 0; $i < count($man); $i++){
        $uuid = $msg_m->add($man[$i], $elem);
        $msg_m->adddb($uuid);
      }
    }elseif($result['status'] == 'success'){
      $msg = "邀请【" . $param['info']['person_invite'] . "】账户邀请邮件" . "发送成功";
      $elem = array(
        'eid' => uuid(),
        'type' => 'dept_email',
        'send_time' => time(),
        'content' => $msg,
      );
      for($i = 0; $i < count($man); $i++){
        $uuid = $msg_m->add($man[$i], $elem);
        $msg_m->adddb($uuid);
      }
    }
  }
  /**
   * 年度目标弹窗提醒
   *
   * @task    2022-05575 https://oa.vc800.com/?/task/view/about/h37wkhwyro
   * @param   string    $value [description]
   * @author  蒋璨 <@pepm.com.cn>
   * @version release/v5.5
   */
  function the_annual_reminder($eid = null){
    $em = load("m/entity_m");

    // $current_year = date("Y");
    $uid = $_POST['uid'] ? $_POST['uid'] : UID;

    $the_annual_target = $em->get("AND `del` = 0 AND `data`->>'$.designated_person' = '$uid' AND `type` = 'the_annual_target'")[0];
    $the_annual_target['data'] = _decode($the_annual_target['data']);
    
    if(!$the_annual_target['data']) return false;

    // $repeattx = $the_annual_target['data']['repeattx'];
    // $hope_year = $the_annual_target['data']['hope_year'];
    // $repeattx_day = date("Y-m-d", strtotime("$current_year-12-31"));
    // $repeattx_day = date("Y-m-d", strtotime("+$repeattx day", strtotime($hope_year)));
    
    // $is_true = date("Y-m-d") >= $hope_year && date("Y-m-d") <= $repeattx_day ? true : false;
    // if(!$is_true) return false;
    
    
    if(!$the_annual_target['data']['daily_reminders'][date("Y-m-d")]){
      $the_annual_target['data']['daily_reminders'][date("Y-m-d")] = true;
      $em->replace($the_annual_target,$the_annual_target['type'],'update',false,false,false,false);
      ajax_return(["hava_target"=>true,"target"=>$the_annual_target['data']['target']]);
    }
    else
      ajax_return(["hava_target"=>false]);
  }

  // 此函数用于新增目标时新增任务
  function target_hook_task($eid){
    $em = load("m/entity_m");

    $elem = $em->get_one($eid);
    $elem['data'] = _decode($elem['data']);

    $time = date("Y").'-'.'01'.'-'.'01';
    $data = array(
      'name' => '年度目标',
      'description' => $elem['data']['target'],
      'date' => $elem['data']['hope_year'],
      'member' => $elem['data']['designated_person'],
      'member_label' => $elem['data']['designated_person_label'],
      'importance' => '重要不急',
      'importance_label' => '重要不急',
      'state' => '待处理',
      'state_label' => '待处理',
      'monitor_mark' => '1',
      'creat_date' => date('Y-m-d H:i:s'),
      'dealline' => date("Y-m-d", strtotime("-1 day",strtotime("+1 year", strtotime(date("Y").'-'.'01'.'-'.'01')))),
    );
    $duties['data'] = $data;
    $duties['uuid'] = uuid();
    $duties['type'] = 'duties';

    $em->replace($duties,$duties['type'],'add', false, false, false, false);
  }

  /**
   * 外部填报“手动催办”逻辑
   *
   * @task    2022-05571 https://oa.vc800.com/?/task/view/about/h37vw3zb80
   * @param   string    $value [description]
   * @author  蒋璨 <@pepm.com.cn>
   * @version release/v5.5
   */
  function manual_urged($eid = null){
    $em = load("m/entity_m");
    $open_m = load('m/open_m');
    $elem = $em->get_one($eid);
    $elem['data'] = _decode($elem['data']);

    $company_uuid = $elem['data']['company'];
    $company = $em->get_one($company_uuid);
    $company['data'] = _decode($company['data']);

    // 收件邮箱
    $_pm_inputer = $company['data']['_pm_inputer'];
    //抄送邮箱
    $cc = $company['data']['_cc_inputer']?explode(',',$company['data']['_cc_inputer']):'';
    $team_manager = $company['data']['team_manager'];
    $extra_data = [
      'vendor' => 'dalton',
      'func' => 'manual_urged_back',
      'company_post_filling_title' => $elem['data']['name'],
      'company_post_filling_uuid' => $elem['uuid'],
    ];
    // 打开外部填报权限
    $data = array('eid' => $elem['uuid'], 'mod' => 'company_post_filling', 'view' => 'company_post_filling');
    $new = array('data' => $data, 'view' => 'company_post_filling', 'fromid' => $elem['uuid'], 'sid' => WEBSID, 'create_time' => time());
    $uuid_open = $open_m->add($new);
        
    foreach(explode(',',$_pm_inputer) as $k => $v) {
      $mail_data = [
        'email'      => $v,
        'subject'    => "请完成【" . $elem['data']['name'] . "】的填写（请尽快）", //标题
        'msgid'      => '',
        'link'       => WWW . '/?' . '/open/id/' .$uuid_open,
        'sid'        => WEBSID,
        'uid'        => $company['data']['team_manager'],
        'email_view' => "",
        'cc'=>$cc, //添加抄送人员邮箱 
        'email_list' => [],
        'remind'     => null,
      ];
      load('m/queue_m')->add_mail($mail_data,'','',$extra_data);
    }
    ajax_return(["ok"=>ture]);
  }
  
  /**
   * 外部填报“定时催办”逻辑
   *
   * @task    2022-05571 https://oa.vc800.com/?/task/view/about/h37vw3zb80
   * @param   string    $value [description]
   * @author  蒋璨 <@pepm.com.cn>
   * @version release/v5.5
   */
  // 这个函数需要配置成定时任务，每个月的15号执行一次
  function timing_urged($eid = null){
    $em = load('m/entity_m');
    $open_m = load('m/open_m');
    $now_year = date('Y');
    $now_month = date('m');
    $month = ['1','4','7','10'];
    $elem = $em->get("AND `del` = 0 AND `type` = 'company' AND `data`->>'$.company_state' = '已投'");
  
    // 在每个季度开始的第15天 既 1-15 、4-15 、7-15 、 10-15
    
    if (!(in_array(date('m'),$month) && date('d') == '1')) return false;
    
    $quarter = $this->return_quarter(date("Y-m"));
    if($quarter == '年度'){
      $now_year -= 1;
    }
    foreach ($elem as $key => $val){
      $elem_data = _decode($val['data']);
      if (!$elem_data['_pm_inputer']) continue;
      $company_post_filling = $em->get("AND `type` = 'company_post_filling' AND `data`->>'$.company' = '{$val['uuid']}' AND `data`->'$.year' = '$now_year' AND `data`->>'$.season' = '$quarter' AND `del` = 0");
      
      if(empty($company_post_filling)){
        $data = [
          'company' => $val['uuid'],
          'company_label' => $elem_data['name'],
          'year' => $now_year,
          'year_label' => $now_year,
          'season' => $quarter,
          'season_label' => $quarter,
          'not_to_fill_out' => false
        ];
        $post_filling['data'] = $data;
        
        $new_uuid=$em->replace($post_filling,'company_post_filling','add',false,false,false,false);

        $extra_data = [
          'vendor' => 'dalton',
          'func' => 'manual_urged_back',
          'timing_urged' => true,
          'company_post_filling_title' => $elem_data['name'].'-'.$now_year.$quarter.'报告',
          'company_post_filling_uuid' => $new_uuid,
        ];
        //抄送邮箱
        $cc = $elem_data['_cc_inputer']?explode(',',$elem_data['_cc_inputer']):'';
        
        // 打开外部填报权限
        $data = array('eid' => $new_uuid, 'mod' => 'company_post_filling', 'view' => 'company_post_filling');
        $new = array('data' => $data, 'view' => 'company_post_filling', 'fromid' => $new_uuid, 'sid' => WEBSID, 'create_time' => time());
        $uuid_open = $open_m->add($new);
        
        foreach(explode(',',$elem_data['_pm_inputer']) as $k => $v) {
          $mail_data = [
            'email'      => $v,
            'subject'    => "请完成【" . $elem_data['name'].'-'.$now_year.$quarter.'报告' . "】的填写", //标题
            'msgid'      => '',
            'link'       => WWW . '/?' . '/open/id/' .$uuid_open,
            'sid'        => WEBSID,
            'uid'        => $elem_data['team_manager_label'],
            'email_view' => "",
            'email_list' => [],
            'cc'=>$cc, //添加抄送人员邮箱 
            'related_id' => $new_uuid,
          ];
        
          $result_email=load('m/queue_m')->add_mail($mail_data,'','',$extra_data);  
          // 脚本运行时插入发送时间和改变状态
          if ($result_email){
            $update_data =[
              'update_time1'=>$now_year.$quarter,
              '_pm_state'=>'已创建邮件',
            ];
            $elem[$key]['data'] = $update_data;
            $em->replace($elem[$key],'company','update',false,false,false,false);
          }
        }
      }
    }
  }

  /**
   * 外部填报邮件抓取
   *
   * @task    2022-05571 https://oa.vc800.com/?/task/view/about/h37vw3zb80
   * @param   string    $value [description]
   * @author  蒋璨 <@pepm.com.cn>
   * @version release/v5.5
   */
  function manual_urged_back($result){
    define('WEBSID','ggi7uujrxq');
    $msg_m = load('m/msg_m');
    $queue_m = load('m/queue_m');
    $entity_m = load('m/entity_m');
    $space_usr_m = load('m/space_usr_m');

    $param = $queue_m->get($result['mail_uuid']);
    $param['info'] = _decode($param['info']);
    
    // 通过填报中的company获取uuid,最终目的改变状态
    $post_data = $entity_m->get_one($param['info']['company_post_filling_uuid']);
    $post_data['data'] = _decode($post_data['data']);
    $company_uuid = $post_data['data']['company'];
    $company = $entity_m->get_one($company_uuid);
    
    $man = array_unique(array_merge($space_usr_m->get_by_role('Admin','uid'),(array)$param['uid']));
    
    if($result['status'] == 'fail'){
      // 用于改变pm状态
      $send_result = ['_pm_state'=>'发送失败，请留意'];
      $company['data'] = $send_result;
      $entity_m->replace($company,'company','update',false,false,false,false);
      
      $msg = "【" . $param['info']['company_post_filling_title'] . "】手动催办外部填报邮件发送失败，请留意！";
      $msg = $param['info']['timing_urged']? "【" . $param['info']['company_post_filling_title'] . "】定时催办外部填报邮件发送失败，请留意！":$msg;
      $msg = $param['info']['a_week_later_check']? "【" . $param['info']['company_post_filling_title'] . "】定时催办外部填报邮件发送失败，请留意！":$msg;
      $elem = array(
        'eid' => uuid(),
        'type' => 'dept_email',
        'send_time' => time(),
        'content' => $msg,
        'sid' => WEBSID,
      );
      for($i = 0; $i < count($man); $i++){
        $uuid = $msg_m->add($man[$i], $elem);
        $msg_m->adddb($uuid);
      }
    }elseif($result['status'] == 'success'){

      // 用于改变pm状态
      $send_result = ['_pm_state'=>'发送成功，待填报'];
      $company['data'] = $send_result;
      $entity_m->replace($company,'company','update',false,false,false,false);
      
      $msg = "【" . $param['info']['company_post_filling_title'] . "】手动催办外部填报邮件发送成功！";
      $msg = $param['info']['timing_urged']? "【" . $param['info']['company_post_filling_title'] . "】定时催办外部填报邮件发送成功！":$msg;
      $msg = $param['info']['a_week_later_check']? "【" . $param['info']['company_post_filling_title'] . "】定时催办外部填报邮件发送成功！":$msg;
      $elem = array(
        'eid' => uuid(),
        'type' => 'dept_email',
        'send_time' => time(),
        'content' => $msg,
        'sid' => WEBSID,
      );
      /*
      for($i = 0; $i < count($man); $i++){
        $uuid = $msg_m->add($man[$i], $elem);
        $msg_m->adddb($uuid);
      }
      */
    }
  }

  /**
   * 外部填报“定时催办”逻辑
   *
   * @task    2022-05571 https://oa.vc800.com/?/task/view/about/h37vw3zb80
   * @param   string    $value [description]
   * @author  蒋璨 <@pepm.com.cn>
   * @version release/v5.5
   */
  // 一个星期后如果没有填报提醒一次
  // 这个函数需要配置成定时任务，每个月的15号后7天执行一次
  function a_week_later_check(){
    $em = load('m/entity_m');
    $open_m = load('m/open_m');
    $now_year = date('Y');
    $now_month = date('m');
    $month = ['1','4','7','10'];
    $elem = $em->get("AND `del` = 0 AND `type` = 'company' AND `data`->>'$.company_state' = '已投'");
    
    // 如果是15号的后一个星期
    if(date("Y-m-d") == date("Y-m-d", strtotime("+1 week", strtotime(date('Y-m').'-'.'15')))){
      
      $quarter = $this->return_quarter(date("Y-m"));
      if($quarter == '年度'){
        $now_year -= 1;
      }
      foreach ($elem as $key => $val){
        $elem_data = _decode($val['data']);
        if (!$elem_data['_pm_inputer']) continue;
        
        $company_post_filling = $em->get("AND `type` = 'company_post_filling' AND `data`->>'$.company' = '{$val['uuid']}' AND `data`->'$.year' = '$now_year' AND `data`->>'$.season' = '$quarter' AND `del` = 0")[0];
        //  一个星期后去查是否有company_post_filling如果有便去查是否有邮件 是否发送成功？
        //抄送邮箱
        $cc = $elem_data['_cc_inputer']?explode(',',$elem_data['_cc_inputer']):'';
        if(!empty($company_post_filling)){
          
          $emailUUID = $company_post_filling["uuid"];
          $sql = "SELECT * FROM `queue` WHERE `type`='email' AND `sid`='$sid' AND `related_id`='$emailUUID' ORDER BY `send_time` DESC LIMIT 0,1";
          $email = $em->db->query($sql)[0];
          // 回调函数
          $extra_data = [
            'vendor' => 'dalton',
            'func' => 'manual_urged_back',
            'a_week_later_check' => true,
            'company_post_filling_title' => $elem_data['name'].'-'.$now_year.$quarter.'报告',
            'company_post_filling_uuid' => $emailUUID,
          ];
          // 打开外部填报权限
          $data = array('eid' => $emailUUID, 'mod' => 'company_post_filling', 'view' => 'company_post_filling');
          $new = array('data' => $data, 'view' => 'company_post_filling', 'fromid' => $emailUUID, 'sid' => WEBSID, 'create_time' => time());
          $uuid_open = $open_m->add($new);
          
          if(!$email['status'] == ' sent' || empty($email)){
            foreach(explode(',',$elem_data['_pm_inputer']) as $k => $v) {
              $mail_data = [
                'email'      => $v,
                'subject'    => "请完成【" . $elem_data['name'].'-'.$now_year.$quarter.'报告' . "】的填写", //标题
                'msgid'      => '',
                'link'       => WWW . '/?' . '/open/id/' .$uuid_open,
                'sid'        => WEBSID,
                'uid'        => $elem_data['team_manager_label'],
                'email_view' => "",
                'email_list' => [],
                'cc'=>$cc, //添加抄送人员邮箱 
                'related_id' => $emailUUID
              ];

              $email_result = load('m/queue_m')->add_mail($mail_data,'','',$extra_data);
              if($email_result){
                $update_data =[
                  'update_time1'=>$now_year.$quarter,
                  '_pm_state'=>'已创建邮件',
                ];
                $elem[$key]['data'] = $update_data;
                $em->replace($elem[$key],'company','update',false,false,false,false);
              }
            }
          }
        }else{
          $data = [
            'company' => $val['uuid'],
            'company_label' => $elem_data['name'],
            'year' => $now_year,
            'year_label' => $now_year,
            'season' => $quarter,
            'season_label' => $quarter,
            'not_to_fill_out' => false
          ];
          
          $post_filling['data'] = $data;

          $new_uuid = $em->replace($post_filling,'company_post_filling','add',false,false,false,false);
          // 回调函数
          $extra_data = [
            'vendor' => 'dalton',
            'func' => 'manual_urged_back',
            'a_week_later_check' => true,
            'company_post_filling_title' => $elem_data['name'].'-'.$now_year.$quarter.'报告',
            'company_post_filling_uuid' => $new_uuid,
          ];
          // 打开外部填报权限
          $data = array('eid' => $new_uuid, 'mod' => 'company_post_filling', 'view' => 'company_post_filling');
          $new = array('data' => $data, 'view' => 'company_post_filling', 'fromid' => $new_uuid, 'sid' => WEBSID, 'create_time' => time());
          $uuid_open = $open_m->add($new);
          
          foreach(explode(',',$elem_data['_pm_inputer']) as $k => $v) {
            $mail_data = [
              'email'      => $v,
              'subject'    => "请完成【" . $elem_data['name'].'-'.$now_year.$quarter.'报告' . "】的填写", //标题
              'msgid'      => '',
              'link'       => WWW . '/?' . '/open/id/' .$uuid_open,
              'sid'        => WEBSID,
              'uid'        => $elem_data['team_manager_label'],
              'email_view' => "",
              'email_list' => [],
              'cc'=>$cc, //添加抄送人员邮箱 
              'related_id' => $new_uuid,
            ];
            $email_result = load('m/queue_m')->add_mail($mail_data,'','',$extra_data);
            // 如果添加邮件成功，更新状态
            if($email_result){
              $update_data =[
                'update_time1'=>$now_year.$quarter,
                '_pm_state'=>'已创建邮件',
              ];
              $elem[$key]['data'] = $update_data;
              $em->replace($elem[$key],'company','update',false,false,false,false);
            }
          }
        }
      }
    }
  }

  // 此函数用于返回上个季度
  function return_quarter($time){
    $time = explode('-',$time);

    if($time[1] >= 1 && $time[1] <= 3){
      // $quarter = 'Q1';
      $quarter = '年度';
    }elseif($time[1] >= 4 && $time[1] <= 6){
      // $quarter = 'Q2';
      $quarter = 'Q1';
    }elseif($time[1] >= 7 && $time[1] <= 9){
      // $quarter = 'Q3';
      $quarter = 'Q2';
    }elseif($time[1] >= 10 && $time[1] <= 12){
      // $quarter = '年度';
      $quarter = 'Q3';
    }

    return $quarter;
  }
  
  /*
   * 初始化外部填报数据
   */
  function create_pm($param = [])
  {
    $sid = $param['sid'];
    define('WEBSID',$sid);
    $www=$param['info']['www'];

    $entity_m = load('m/entity_m');
    $open_m = load('m/open_m');
    $queue_m = load('m/queue_m');
    $space_m = load('m/space_m');
    $config_m = load('m/config_m');
    $uuid = $param['related_id'];
    $config = $config_m->key($param['related_type']);//pm
    $pm_clear = $config['ext']['_pm_clear'];
    $rel_list = $config['ext']['_rel_list'];
    // 默认三个信息不继承
    $pm_clear[] = 'year';
    $pm_clear[] = 'season';
    $pm_clear[] = '_pm_end_time';
    $pm_clear[] = 'filledby';

    $default_val_set = $config['ext']['default_val_set'];
    //获取外部填报对应的模块
    foreach($config['item'] as $item){
      if($item['type'] == 'data'){
        $pm_mod = $item['ext']['mod'];
        //$pm_filter = $item['ext']['filter']?:['_rel'=>'eid'];
        break;
      }
    }

    $send_time = date("Y-m-d H:i:s",time());
    // 获取当前项目
    $entity = $entity_m->get($uuid);
    $entity_config = $config_m->key($entity['type']);//after_all
    $entity_config_keys = array_keys($entity_config['item']);

    //--获取填报人
    $emails = explode(',', _decode($entity['data'])['_pm_inputer']);
    //获取抄送人
    $cc = _decode($entity['data'])['_cc_inputer']?explode(',', _decode($entity['data'])['_cc_inputer']):'';
    //新建 after_all
    $data_e = [];
    //默认值
    if($default_val_set){
      $entity_data['data'] = _decode($entity['data']);
      foreach($default_val_set as $kk => $vv){
        if($vv == 'eid'){
          $data_e[$kk] = $uuid;
          $data_e[$kk . '_label'] = $entity['name'];
          continue;
        }
        if(in_array($vv,$entity_config_keys)){
          $data_e[$kk] = $entity_data['data'][$vv]?:'';
          if($entity_data['data'][$vv . '_label']){
            $data_e[$kk . '_label'] = $entity_data['data'][$vv . '_label'];
          }
        }else{
          $data_e[$kk] = $vv;
        }
      }
    }
    if($param['info']['pre_data']){
      foreach ($param['info']['pre_data'] as $ks =>$vs){
        $data_e[$ks] = $vs;
      }
    }
    $data_e['company'] = $uuid;
    $data_e['company_label'] = $entity['name'];
    $rp_config = $config_m->key($pm_mod);//二级返回数据
    $rp_state_key = !empty($rp_config['ext']['param_setting']['state'])?$rp_config['ext']['param_setting']['state']:'_pm_state';
    $data_e = array_merge($data_e, array('_rel' => $uuid, '_rel_view' => $param['related_type'] ,'create_date' => date('Y-m-d'), $rp_state_key => '待填报'));
    $data = array('data' => $data_e, 'uuid' => uuid(), 'type' => $pm_mod, 'name' => NULL);
    
    //获取上一季度
    switch ($data['data']['season']) {
      case '第一季度':
        $last_year = $data['data']['year']-1;
        $last_season = '第四季度';
        break;
      case '第二季度':
        $last_year = $data['data']['year'];
        $last_season = '第一季度';
        break;
      case '第三季度':
        $last_year = $data['data']['year'];
        $last_season = '第二季度';
        break;
      case '第四季度':
        $last_year = $data['data']['year'];
        $last_season = '第三季度';
        break;
    }

    $sql = "SELECT * FROM entity WHERE `sid`='$sid' AND `del`=0 AND `type`='$pm_mod' AND `data`->>'$._rel'='$uuid' AND `data`->>'$.year'='$last_year' and `data`->>'$.season'='$last_season' order by `input_date` limit 0,1";
    $last_data = $entity_m->db->query($sql)[0];

    if($last_data){
      $last_data['data'] = _decode($last_data['data']);

      foreach ($last_data['data'] as $k => $v) {
        if(
          (!in_array($k, $pm_clear)&&
          (!in_array(str_replace('_label', '', $k), $pm_clear)))
        ){
          $data['data'][$k] = $last_data['data'][$k];
        }
      }
    } 

    $uuid_en = $entity_m->replace($data);

    //有上一季度数据时才复制两个模块数据
    if($last_data){
       //处理关联数据
      $this->after_all_rel_rsync($last_data['uuid'], $uuid_en, $sid,$rel_list);
    }

    $extra_data = [];

    $post_filling_contact = $config_m->key('post_filling_contact');
    $extra_data['contact_name'] = $post_filling_contact['name'];
    $extra_data['contact_email'] = $post_filling_contact['email'];
    $extra_data['contact_tel'] = $post_filling_contact['tel'];

    //设置投后管理数据为启用外部填报
    $data = array('eid' => $uuid_en, 'mod' => $pm_mod, 'view' => $pm_mod);
    $new = array('data' => $data, 'view' => $pm_mod, 'fromid' => $uuid_en, 'sid' => WEBSID, 'create_time' => time());
    $uuid_open = $open_m->add($new);
    //发送邮件给填报人
    //--项目简称
    $name = $entity['name'];
    if(!empty($config['title_field'])){
      $name = t($config['title_field'] , _decode($entity['data']) ,'[]');
    }
    $pm_state_key = !empty($config['ext']['param_setting']['state'])?$config['ext']['param_setting']['state']:'_pm_state';
    //--发送邮件相关内容
    foreach($emails as $email){
      //$token = sha1($email.time().SEED);
      if(!filter_var($email, FILTER_VALIDATE_EMAIL))
        continue;
      $space_rs = $space_m->get(" AND `uuid`='" . WEBSID . "' ", 0, 1);
      $organization = $space_rs['0']['name'] ? $space_rs['0']['name'] : 'vc800';
      define('ORG', $organization);
      $remindinfo = array('name' => $name, 'organization' => $organization, 'href' => '?/open/id/' . $uuid_open, 'www' => $www);
      $remind['www']=$www;
      if($config['openlink_title']){
        $remind['openlink_title'] = t($config['openlink_title'], $remindinfo);
      }
      else{
        $remind['openlink_title'] = $organization . '【' . $name . '】数据填报提醒';
      }
      if($config['openlink_button']){
        $remind['openlink_button'] = t($config['openlink_button'], $remindinfo);
      }
      else{
        $remind['openlink_button'] = '数据填报';
      }
      if($config['openlink_content']){
        $remind['body'] = t($config['openlink_content'], $remindinfo);
      }
      $space_rs['0']['data'] = _decode($space_rs['0']['data']);
      $extra_data['logo_email'] = $space_rs['0']['data']['logo_mail'];
      $data = array(
        'email' => $email,
        'subject' => $remind['openlink_title'], //标题
        'href' => '/?/open/id/' . $uuid_open,
        'organization' => ORG,//机构名字
        'remind' => $remind,
        'related_id' => $uuid,
        'www'=>$remind['www'],
        'related_type'=>'pm',
        'pm_state_key'=>$pm_state_key,
        'cc'=>$cc, //添加抄送人员邮箱 
        'extra_data'=>$extra_data,
      );
      $queue_m->add_mail($data, 'file_data_remind');
    }

    $data = array('uuid' => $uuid, 'data' => array('_pm_time' => date('Y-m-d'), $pm_state_key => '已创建邮件','send_time' => $send_time));
    $entity_m->replace($data , null , '', '', false, false , false);
    //直接标记为sent
    $queue_m->update($param['id'], array('status'=>'sent'));
  }
  
  /*
   * 定时脚本-定时填充提醒信息
   */ 
  function financing_to_monitor() {
    $sid = WEBSID;
    $entity_m = load('m/entity_m');
    $config_m = load('m/config_m');
    $config = $config_m->key('_financing_monitor')['ext'];
    $table_name = $this->em->entity_standalone($sid, 'financing');
    $key_allpaymentbl = $this->em->data_field_key('allpaymentbl', $table_name);
    $key_state_label = $this->em->data_field_key('state_label', $table_name);
    $key_team_manager = $this->em->data_field_key('team_manager', $table_name);
    $key_team_manager_label = $this->em->data_field_key('team_manager_label', $table_name);
    $key_name = $this->em->data_field_key('name', $table_name);
    $financing = $entity_m->db->query("select `uuid`,$key_team_manager as team_manager,$key_team_manager_label as team_manager_label,$key_name as name,$key_state_label as state_label from $table_name where `type`='financing' and `sid`='$sid' and `del`=0 and $key_state_label in ('投资交割','投后管理') and $key_allpaymentbl>=100");
    if($financing == []) {
      return;
    }
    $uuid = array_column($financing,'uuid');
    $str = '(';
    foreach($uuid as $k => $v) {
      $str.="'".$v."',";
    }
    $str = rtrim($str,',');
    $str.=')';
    $table_name = $this->em->entity_standalone($sid, '_financing_monitor');
    $key_financing = $this->em->data_field_key('financing', $table_name);
    $key_monitoring_time = $this->em->data_field_key('monitoring_time', $table_name);
    $key_is_email = $this->em->data_field_key('is_email', $table_name);
    $_financing_monitor = $entity_m->db->query("select `uuid`,$key_financing as financing,$key_monitoring_time as monitoring_time,$key_is_email as is_email from $table_name where `type`='_financing_monitor' and `sid`='$sid' and `del`=0 and $key_financing in $str");
    $space_usr_m = load('m/space_usr_m');
    $man = array_unique($space_usr_m->get_by_role('role_gdzymkjjp9','uid'));
    
    $elem = [];
    foreach($financing as $k => $v) {
      if($v['state_label'] == '投资交割') {
        $elem[$k]['data']['financing'] = $v['uuid'];
        $elem[$k]['data']['financing_label'] = $v['name'];
        $elem[$k]['data']['reminder'] = $v['team_manager'];
        $elem[$k]['data']['reminder_label'] = $v['team_manager_label'];
        $elem[$k]['data']['monitoring_time'] = date('Y-m-d',time());
        $elem[$k]['data']['out_of_time'] = 0;
        $elem[$k]['data']['reminder_content'] = '';
        $elem[$k]['data']['boss'] = [];
        foreach($_financing_monitor as $k1 => $v1) {
          if($v1['financing'] == $v['uuid']) {
            $elem[$k]['uuid'] = $v1['uuid'];
            $elem[$k]['data']['is_email'] = $v1['is_email'];
            unset($elem[$k]['data']['monitoring_time']);
            $monitoring_time = explode('-',$v1['monitoring_time']);
            $out_of_time = explode('-',date('Y-m-d',time()));
            $month = abs($monitoring_time[0] - $out_of_time[0]) * 12 + abs($monitoring_time[1] - $out_of_time[1]);
            if($month>=1 && $month<3) {
              $elem[$k]['data']['out_of_time'] = 1;
              $name = ['name'=>$v['name'],'team_manager'=>$v['team_manager_label']];
              $elem[$k]['data']['reminder_content'] = t($config['reminder'],$name);
            }elseif($month>=3) {
              $name = ['name'=>$v['name'],'team_manager'=>$v['team_manager_label']];
              $elem[$k]['data']['out_of_time'] = 3;
              $elem[$k]['data']['reminder_content'] = t($config['email'],$name);
              $elem[$k]['data']['boss'] = $man;
              if($v1['is_email'] != 1) {
                $is_email = $this->send_email_to_boss($man,$v['uuid'],$elem[$k]['data']['reminder_content']);
                if($is_email) {
                  $elem[$k]['data']['is_email'] = 1;
                }
              }
            }else {
              $elem[$k]['data']['out_of_time'] = 0;
            }
          }
        }
        $entity_m->replace($elem[$k] , '_financing_monitor', false, false, false , false);
      }else{
        foreach($_financing_monitor as $k1 => $v1) {
          if($v1['financing'] == $v['uuid']) {
            $elem[$k1]['uuid'] = $v1['uuid'];
            $elem[$k1]['del'] = 1;
            $entity_m->replace($elem[$k1] , '_financing_monitor', false, false, false , false);
          }
        }
      }
    }
      
  }
    //消息提醒
  function the_financing_message_reminder($uid=null) {
    $sid = WEBSID;
    $entity_m = load('m/entity_m');
    $table_name = $this->em->entity_standalone($sid, '_financing_monitor');
    $key_reminder = $this->em->data_field_key('reminder', $table_name);
    $key_reminder_content = $this->em->data_field_key('reminder_content', $table_name);
    $key_out_of_time = $this->em->data_field_key('out_of_time', $table_name);
    $reminder_content = $entity_m->db->query("select $key_reminder_content as reminder_content from $table_name where `type`='_financing_monitor' and `sid`='$sid' and `del`=0 and $key_reminder = '$uid' and $key_out_of_time>0");

    if($reminder_content != []) {
      
      $html = "<span style='font-weight:600;color:red'></span>";
      foreach($reminder_content as $k => $v) {
        
        $html.='</br>'."<div style='text-indent:2em;margin:20px 0px;font-size:15px;'>".$v['reminder_content']."</div>";
      }
      ajax_return(["hava_target"=>true,"target"=>$html]);
    }else {
      ajax_return(["hava_target"=>false]);
    }
    
  }
    //发送邮件给老板
  function send_email_to_boss($boss,$uuid,$reminder_content){
    $notify_to = $boss;
    $data['eid'] = $uuid;
    $data['link']="?/financing/view/about/".$uuid;
    $data['tmpl']='link';
    $data['type']='financing';
    $data['send_time']=time();
    $data['sid']=$sid;
    $data['content']= $reminder_content;
    $res = load('m/msg_m')
      ->set_msg_vendor(['email'])
      ->set_force_mode(true)
      ->batch($notify_to, "add", $data, true);
    if($res != []) {
      return true;
    }else {
      return false;
    }
  }
    // 此函数是定时脚本,用于检查duties模块状态为('待处理','处理中','暂停') 时超过ddl的数据
    function check_duties_ddl(){
      $sid = WEBSID;
    $entity_m = load('m/entity_m');
    $config_m = load('m/config_m');
    $config = $config_m->key('duties')['ext'];
    
    $table_name = $this->em->entity_standalone($sid, 'duties');
    $key_dealline = $this->em->data_field_key('dealline', $table_name);
    $key_name = $this->em->data_field_key('name', $table_name);
    $key_send_member = $this->em->data_field_key('send_member', $table_name);
    $key_send_boss = $this->em->data_field_key('send_boss', $table_name);
    $key_state_label = $this->em->data_field_key('state_label', $table_name);
    $key_team_manager = $this->em->data_field_key('member', $table_name);
    $key_team_manager_label = $this->em->data_field_key('member_label', $table_name);
    $key_company_label = $this->em->data_field_key('company_label', $table_name);
    $key_mark_label = $this->em->data_field_key('monitor_mark', $table_name);
    $mark_time = strtotime('2023-4-23');
    $duties = $entity_m->db->query("select `uuid`,$key_company_label as company_label ,$key_name as name,$key_team_manager as member,$key_team_manager_label as member_label,$key_dealline as ddl,$key_send_member as send_member ,$key_send_boss as send_boss from $table_name where `type`='duties' and `sid`='$sid' and `input_date`> $mark_time and `del`=0 and $key_mark_label is null and  $key_state_label in ('待处理','处理中','暂停') ");
    
  

    foreach($duties as $k => $v){
      $name = ['company'=>$v['company_label'],'name'=>$v['name'],'member'=>$v['member_label']];
      
      if (strtotime($v['ddl'])<time() && time()<strtotime($v['ddl'])+1296000){
        $reminder_content = $v['company_label']?t($config['reminder'],$name):t($config['reminder_two'],$name);

         if ($v['send_member']!= '已抄送'){
           $is_send=$this->send_email_to_sun($v['member'],$v['uuid'],$reminder_content);
           if($is_send){
             $duties[$k]['data']['send_member'] = '已抄送';
          }
        }
      }elseif(time()>strtotime($v['ddl'])+1296000){
        $reminder_content = $v['company_label']?t($config['reminder_boss'],$name):t($config['reminder_boss_two'],$name);
        
        // $boss = 'hay517ou1k'; // 测试人员
        $boss = 'ggjcee8aw6'; // 孙琦总
        
        if ($v['send_boss']!= '已抄送'){
          $is_send= $this->send_email_to_sun($boss,$v['uuid'],$reminder_content);
          if ($is_send){
            $duties[$k]['data']['send_boss'] = '已抄送';
          }
        }
      }
     $entity_m->replace($duties[$k] , 'duties', false, false, false , false);
    }
  }
  //发送邮件和消息
  function send_email_to_sun($man,$uuid,$reminder_content){
    $notify_to = $man;
    $data['eid'] = $uuid;
    $data['link']="?/duties/view/about/".$uuid;
    $data['tmpl']='link';
    $data['type']='duties';
    $data['send_time']=time();
    $data['sid']=$sid;
    $data['content']= $reminder_content;
    $res = load('m/msg_m')
      ->set_msg_vendor(['email'])
      ->set_force_mode(true)
      ->batch($notify_to, "add", $data, true);
    if($res != []) {
      return true;
    }else {
      return false;
    }
  }
  // 此函数是定时脚本,每月一号执行,用于检查financing上个月和上季度每个人交易推进的情况 
  function check_financing_state_monitor(){
    $sid = WEBSID;
    $entity_m = load('m/entity_m');
    $table_name = $this->em->entity_standalone($sid, 'financing');
    $space_usr_m = load('m/space_usr_m');
    $config_m = load('m/config_m');
    $config = $config_m->key('financing')['ext'];
    $key_state_label = $this->em->data_field_key('state_label', $table_name);
    $key_lx_pass_time = $this->em->data_field_key('lx_pass_time', $table_name);
    $key_tj_pass_time = $this->em->data_field_key('tj_pass_time', $table_name);
    $key_jg_pass_time = $this->em->data_field_key('jg_pass_time', $table_name);
    $key_team_manager = $this->em->data_field_key('team_manager', $table_name);
    $month = ['1','4','7','10'];
    $day = ['1'];
    $man = array_unique(array_merge($space_usr_m->get_by_role('role_hdmchc7gbp','uid')));
    $start_time = strtotime(date('Y-m-01 00:00:01',strtotime(date('Y-m-01').'-1 month'))); //上个月一号的时间
    $start_three_time=strtotime(date('Y-m-01 00:00:00',strtotime(date('Y-m-01').'-3 month')));//三个月前一号的时间
    $end_time = strtotime(date('Y-m-d 23:59:59',strtotime(date('Y-m-01').'-1 day')));// 上月最后一天

    // $boss = 'hay517ou1k'; // 测试人员
    $boss = 'ggjcee8aw6'; // 孙琦总
    foreach($man as $k=>$v){
      $userinfo=load('m/usr_m')->get_one($v);
      $name = ['name'=>$userinfo['name']];
      // 只有在1 4 7 10月 的一号
      if(in_array(date('m'),$month) && in_array(date('d'),$day)){

        $financing_lx = $entity_m->db->query("select * from $table_name where sid='$sid' and type = 'financing' and del = 0 and $key_team_manager = '$v'  and $key_lx_pass_time between $start_three_time and $end_time  ");
      
        $financing_tj = $entity_m->db->query("select * from $table_name where sid='$sid' and type = 'financing' and del = 0 and $key_team_manager = '$v'  and $key_tj_pass_time between $start_three_time and $end_time  ");
        
        $financing_jg = $entity_m->db->query("select * from $table_name where sid='$sid' and type = 'financing' and del = 0 and $key_team_manager = '$v'  and $key_jg_pass_time between $start_three_time and $end_time  ");
        //立项为空
        if(empty($financing_lx)){
          $lx_reminder = t($config['reminder_lx'], $name);
          $this->send_monitor_boss($boss,$lx_reminder);
        }
        // 投决为空
        if(empty($financing_tj)){
          $tj_reminder = t($config['reminder_tj'], $name);
          $this->send_monitor_boss($boss,$tj_reminder);
        }
        // 交割为空
        if(empty($financing_jg)){
          $jg_reminder = t($config['reminder_jg'], $name);
          $this->send_monitor_boss($boss,$jg_reminder);
        }
      } 
      // 每月一号
      $financing_bp = $entity_m->db->query("select * from $table_name where sid='$sid' and type = 'financing' and del = 0 and input_people = '$v'  and input_date between $start_time and $end_time  ");
      
      // BP筛选为空
      if(empty($financing_bp)){
        $bp_reminder = t($config['reminder_bp'], $name);
        $this->send_monitor_boss($boss,$bp_reminder);
      }
    }
  }
  // 会在check_financing_state_monitor 方法中被调用
  function send_monitor_boss($boss,$reminder_content){
    $notify_to = $boss;
    $data['link']="?/msg/all";
    $data['tmpl']='link';
    $data['send_time']=time();
    $data['sid']=$sid;
    $data['content']= $reminder_content;
    $res = load('m/msg_m')
      ->set_msg_vendor(['email'])
      ->set_force_mode(true)
      ->batch($notify_to, "add", $data, true);
    if($res != []) {
      return true;
    }else {
      return false;
    }
  }
  // 此方法用于退出之后的项目重新投资的改变主体状态
  function add_financing_update_company($eid){
    $sid =WEBSID;
    $elem = $this->em->get_one($eid);
    $elem['data'] = _decode($elem['data']);
    $company_id = $elem['data']['company'];
    
    $company_data=$this->em->get(" AND `del`=0 AND `sid`='$sid' AND `type`='company'  AND uuid='$company_id'")[0];

    if($company_data){
      $company_data['data'] = _decode($company_data['data']);
      if($company_data['data']['company_state_label'] == '退出'){
          $company_data['data']['company_state'] = '未投';
          $company_data['data']['company_state_label'] = '未投';

          $this->em->replace($company_data , 'company', false, false, false , false);;
          
        }
    }
  }
  // 外部填报，填报方提交之后会给史明月发消息
  function reback_email($eid){
    $sid = WEBSID;
    $elem = $this->em->get_one($eid);
    
    $elem_data = _decode($elem['data']);
    $company = $this->em->get_one($elem_data['company']);
    $state = ['_pm_state'=>'已提交'];
    $company['data'] = $state;
    $this->em->replace($company , 'company', false, false, false , false);
    
    $notify_to = 'hdmfnq3viu';  // 史明月
    $data['link']='?/company_post_filling/view/about/'.$elem['uuid'];
    $data['tmpl']='link';
    $data['send_time']=time();
    $data['sid']=$sid;
    $data['content']= $elem['name'].'填报数据已提交';
    load('m/msg_m')
      ->set_msg_vendor(['email'])
      ->set_force_mode(true)
      ->batch($notify_to, "add", $data, true);
  }
  // 此方法用于检查日程创建选择会议室时的时间冲突
  function check_room_time($data =[]){
    $sid = WEBSID;
    $em = load("m/entity_m");
    $space_usr_m = load('m/space_usr_m');
    // 获取mod 、值 、字段标示  
    $type = $_POST['type'] ? $_POST['type'] : $data['type'];
    $start_time = $_POST['start_time'] ? $_POST['start_time'] : $data['start_time'];
    $end_time = $_POST['end_time'] ? $_POST['end_time'] : $data['end_time'];
    $address = $_POST['address'] ? $_POST['address'] : $data['address'];
    
    $table_name = $em->entity_standalone($sid, $type);
    $key_address = $em->data_field_key('address_label', $table_name);
    $entities = $em->get(" AND `del`=0 AND `type` = '$type' AND $key_address = '$address'");
    if(!$entities) return false;

    $is_repeated = false;
    $has_start_time = '';
    $has_end_time = '';
    $name = '';
    foreach($entities as $k => $v){
      $data = _decode($v['data']);
      $date_on = strtotime($data['date_on']);
      $date_off = strtotime($data['date_off']);
      $uuid = $entities[$k]['input_people'];
      if (($start_time>=$date_on && $date_off>$start_time) ||($end_time>$date_on && $date_off>=$end_time) || ($date_on>=$start_time && $end_time>=$date_off)){
        $is_repeated = true;
        $has_start_time = $data['date_on'];
        $has_end_time = $data['date_off'];
        $user=$space_usr_m->get(" and sid = '$sid' and uid = '$uuid'")[0];
        $user_data = _decode($user['data']);
        $name = $user_data['name'];
        break;
      }
    }
    ajax_return(["repeat"=>$is_repeated,"start_time"=>$has_start_time,"end_time"=>$has_end_time,"name"=>$name]);
  }
  
  //重建模块权限
   function clean_priv($sid){
    $em = load("m/entity_m");
    $mods = ['lpcompany'];

    foreach ($mods as $mod) {
      $rs = $em->get(" and type='".$mod."' and del=0", 0,9999);

      foreach($rs as $r){
        $r['data'] = _decode($r['data']);
        $r['data']['privilege'] = $privilege = $em->priv($r, $mod);
        $r['privilege'] = implode(',', $privilege['list']);
        $em->replace_bare($r);
      }
    }
  }
  
    // 此方法用于提醒 sz_personnel 模块中的人员 回上海开会
  function msg_sz_person(){
    $sid = WEBSID;
    $month = ['3','9'];
    $day = ['20','21','22','23','24'];
    $em = load("m/entity_m");
    $p_data=$em->db->query("select * from entity where sid='$sid' and type = 'sz_personnel' and del = 0 ")[0];
    $p_data['data'] = _decode($p_data['data']);
    $people = $p_data['data']['name'];
    
    if(in_array(date('m'),$month) && in_array(date('d'),$day)){
      $notify_to = $people;
      $data['send_time']=time();
      $data['sid']=$sid;
      $data['content']= '请下季度第一周回上海开会！';
      load('m/msg_m')
        ->set_msg_vendor(['email'])
        ->set_force_mode(true)
        ->batch($notify_to, "add", $data, true);
    }else{
      return false;
    }
  }
  // 此方法用于更新填充基金明细中的数据
  function update_fund_detail_all($eid){
    $sid = WEBSID;
    $em = load("m/entity_m");
    $f_data = $em->get_one($eid);
    $f_data['data'] = _decode($f_data['data']);
    $company = $f_data['data']['company'];
    $financing = $f_data['uuid'];
    
    
    $sql = "SELECT * FROM `entity` WHERE `type`='fund_detail_all' AND `sid`='$sid'  AND `del`= 0 AND `data`->>'$.company'='$company' AND `data`->>'$.financing'='$financing'";
    $fund_detail_data = $em->db->query($sql);
    
    foreach($fund_detail_data as $k=>$v){
      $elem_data = $v['data'];
      
      $update_data = [
        'actual_financing_amount'=>$f_data['data']['actual_financing_amount'],
        'transfer_amount'=>$f_data['data']['transfer_amount'],
        'fin_current_value'=>$f_data['data']['fin_current_value'],
        'actual_post_value'=>$f_data['data']['actual_post_value'],
        'gain_capital'=>$f_data['data']['gain_capital'],
        'registered_capital'=>$f_data['data']['registered_capital'],
        'sfdsfff'=>$f_data['data']['sfdsfff'],
        'dsfjgmc'=>$f_data['data']['dsfjgmc'],
        'dsffy'=>$f_data['data']['dsffy'],
        'dsfjsfs'=>$f_data['data']['dsfjsfs'],
        'dsfjsfs_label'=>$f_data['data']['dsfjsfs_label'],
        'dsfjsfsbz'=>$f_data['data']['dsfjsfsbz'],
        'fin_lp'=>$f_data['data']['fin_lp'],
        'remarks'=>$f_data['data']['remarks'],
      ];
      $fund_detail_data[$k]['data'] = $update_data;

      $em->replace($fund_detail_data[$k],'fund_detail_all','update',false,false,false,false);
    }     
  }
  // 此方法用于缴付确认
  function sure_fund_money($eid = null){
    $sid = WEBSID;
    $em = load("m/entity_m");
    $result=$em->get_one($eid);
    $userinfo=load('m/usr_m')->get_one(UID);
    $result['data'] = _decode($result['data']);
    $name = $userinfo['name'];
    $result['data']['paymentstatus'] = '已收款';
    $result['data']['qrr'] = UID;
    $result['data']['qrr_label'] = $name;
    $result['data']['qrtime'] = date('Y-m-d H:i:s',time());
    
    $update_result=$em->replace_bare($result);
    if($update_result){
      ajax_return(['ok'=>'true']);
    }
  }
  // 项目退出时，把所有的交易都设置为退出状态
  function update_financing_state($flow,$data){
    $sid = WEBSID;
    $entity_m = load('m/entity_m');
    
    $company_uuid = $data['form_data']['company'];
    $sql = "SELECT * FROM entity WHERE `sid`='$sid' AND `del`=0 AND `type`='financing' AND `data`->>'$.company'='$company_uuid'";
    $financing = $entity_m->db->query($sql);
    foreach($financing as $k=>$v){
      $financing[$k]['data'] = _decode($v['data']);
      $financing[$k]['data']['state'] = '退出交割';
      $financing[$k]['data']['state_label'] = '退出交割';
      $entity_m->replace($financing[$k],'financing','update', false, false, false, false);
    }
  }
  /**
   * @description: 2023-13599 收益分配通知书-邮件及附件通知
   * @param {*} $sid
   * @return {*}
   * @author: 蒋震 <zjiang@pepm.com.cn>
   * @version: release/v6.6
   */
  // 财务是否确认，确认后发送待办消息
  function caiwu_is_confirm($eid){
    $sid = WEBSID;
    
    $role_ggcczib9k4 = $this->cm->db->query("select `uuid` from `config` where `type`='role' and `sid`='$sid' and `key`='role_ggcczib9k4 '")[0]['uuid'];

    $role_irallocation = $this->cm->db->query("select `uuid` from `config` where `type`='role' and `sid`='$sid' and `key`='role_irallocation '")[0]['uuid'];

    $this->space_usr_m = load("m/space_usr_m");
    $uid = UID;
    $role = $this->space_usr_m->db->query("select * from `space_usr` where `del`=0 and `sid`='$sid' and `uid`='$uid'")[0];
    $role['data'] = _decode($role['data']);
    //如果用户为财务
    if($role_ggcczib9k4 && in_array($role_ggcczib9k4,$role['data']['role'])) {
      $elem = $this->em->get($eid);
      $elem['data'] = _decode($elem['data']);
      //如果财务已确认
      if($elem['data']['cwconfirm'] == "已确认") {
        //填充通知书到文件
        $file_uuid = $this->create_notice_of_income_distribution($sid,$eid);
        //文件绑定数据
        $elem['data']['preview_file_uuid'] = $file_uuid['preview_file_uuid'];
        $elem['data']['annex_uuid'] = $file_uuid['annex_uuid'];
        //删除已有待办
        if($elem['data']['msg_uuid']) {
          $msg_m = load('m/msg_m');
          foreach($elem['data']['msg_uuid'] as $k => $v) {
            $msg_m->del($v);
          }
          
        }

        //发送待办消息
        $msg_uuid = $this->send_msg($eid);
        //待办消息绑定数据
        $elem['data']['msg_uuid'] = $msg_uuid;
        $this->em->replace($elem , $elem['type'], false, false, false , false);
      }
    }elseif ($role_irallocation && in_array($role_irallocation,$role['data']['role'])) {
      $elem = $this->em->get($eid);
      $elem['data'] = _decode($elem['data']);

      //如果IR已确认 待办变已办
      if($elem['data']['irconfirm'] == '已确认' || $elem['data']['irconfirm'] == '发送成功') {
        $msg_m = load('m/msg_m');
        foreach($elem['data']['msg_uuid'] as $k => $v) {
          $msg = $msg_m->get($v);
          $msg['data'] = _decode($msg['data']);

          $msg['event_status'] = 'close';
          $msg['update_date'] = time();
          //$msg['is_read'] = 0;
          

          $msg_m->update($v,$msg);
        }

      }
      
    }
  }

  //发送待办通知
  function send_msg($eid) {
    $sid = WEBSID;
    $role_irallocation = $this->cm->db->query("select `uuid` from `config` where `type`='role' and `sid`='$sid' and `key`='role_irallocation '")[0]['uuid'];
    $this->space_usr_m = load("m/space_usr_m");
    $role = $this->space_usr_m->db->query("select * from `space_usr` where `del`=0 and `sid`='$sid'");
    $notify_to = [];
    foreach($role as $k => $v) {
      $data = _decode($v["data"]);
      if(in_array($role_irallocation,$data['role'])) {
        $notify_to[] = $v['uid'];
      }
    }

    if($notify_to == []) {
      return;
    }

    $content = $this->cm->key("fund_allocation")["ext"]["msg"];
    $elem = $this->em->get($eid);
    $elem['data'] = _decode($elem['data']);
    $lpinvestor = $elem['data']['lpinvestor'];
    $lpinvestor = ['lpinvestor'=>$lpinvestor];
    $content = t($content,$lpinvestor);
    
    $msg = [];
    //消息格式
    $msg['eid'] = $eid;
    $msg['link'] = '?/fund_allocation/view/about/'.$eid;
    $msg['tmpl'] = 'link';
    $msg['type'] = 'fund_allocation';
    $msg['send_time'] = time();
    $msg['sid'] = $sid;
    $msg['content'] = $content;
    //$msg['exports']['top_news'] = $top_lists;
    $msg_m = load('m/msg_m');

    $msg_uuid = [];
    for($i = 0; $i < count($notify_to); $i++){
      //发送消息
      $result_uuid=$msg_m
       ->set_force_mode(true)
       ->set_msg_vendor('msg')
       ->batch($notify_to[$i], "add", $msg, true);

      $uuid = $result_uuid[0];
      $elem = $msg_m->get($uuid);
      $elem['data'] = _decode($elem['data']);
      $elem['event_status'] = 'open';
      $elem['role'] = 'executor';
      $elem['data']['link_by'] = $elem['data']['link'];

      $elem['from_id'] = UID;
      $elem['to_id'] = $notify_to[$i];
        
      //$elem['data']['cancel_top_flow_msg'] = null;

      $msg_m->update($uuid,$elem);

      $msg_uuid[] = $uuid;
    }

    return $msg_uuid;

    
  }  
  
  //生成预览文件 生成附件 收益分配通知书
  function create_notice_of_income_distribution($sid,$eid) {
    $sid = WEBSID;
    $elem = $this->em->get($eid);
    $elem['data'] = _decode($elem['data']);
    $this->fm = load("m/file_m");
    $file_tmp_uuid = $this->cm->key("fund_allocation")['word_export']['word_file'][1];

    $file_tmp = $this->fm->get($file_tmp_uuid);
    $file_tmp_path = $file_tmp['file'];
    $this->fm->dse_decrypt($file_tmp_path);
    $tmp = new \PhpOffice\PhpWord\TemplateProcessor($file_tmp_path);//打开模板
    //\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
    
    $config = $this->cm->key("fund_allocation")['ext']['word_fill_field'];
    foreach($config['normal'] as $k => $v) {
      if($elem['data'][$k]) {
        if(is_numeric($elem['data'][$k])) {
          $tmp->setValue($k, str_replace(".00",'',number_format($elem['data'][$k],2)));
        }else {
          $tmp->setValue($k, $elem['data'][$k]);
        }
        
      }else {
        $tmp->setValue($k, $v);
      }
      
    }

    foreach($config['file'] as $k => $v) {
      if($elem['data'][$k]) {
        $img_name_arr = explode("_",$elem['data'][$k]);
        $img_name = $img_name_arr[count($img_name_arr)-1];
        $cmd = "find ../images/".$sid."/".date("Ym")."/ -name "."'".$img_name."'";
        $path = exec($cmd);
        $tmp->setImageValue($k,['path'=>$path,'width'=>200,'height'=>200,'border'=>0]);
      }else {
        $tmp->setValue($k, $v);
      }
    }

    foreach($config['date'] as $k => $v) {
      if($elem['data'][$k]) {
        $tmp->setValue($k, date("Y年m月d日",strtotime($elem['data'][$k])));
      }else {
        $tmp->setValue($k, $v);
      }
    }

    $new_name = $elem['data']['emailtitle'];
    //$filename = iconv("utf-8","gb2312",$new_name);   //防止有的浏览器下载之后中文乱码

    $file_uuid = uuid();


    $path = "../upload/files/".$sid."/".date("Ym")."/".$file_uuid;
    b2mkdir($path);
    $save_path = $path."/".$file_uuid;

    $tmp->saveAs($save_path);


    $size = filesize(APP."../upload/files/".$sid."/".date("Ym")."/".$file_uuid."/".$file_uuid);
    $file = array(
      'uuid' => $file_uuid,
      'eid' => $eid,
      'file'=> "../upload/files/".$sid."/".date("Ym")."/".$file_uuid."/".$file_uuid,
      'type'=>'docx',
      'data'=>'{"path":""}',
      'uid'=> uid,
      'sid'=> $sid,
      'name' => $new_name,
      'size' => $size,
      //'create_people'=>$sender,
      //'update_people'=>$sender,
      'create_date'=>time(),
      //'update_date' => $upload_time,
      'doc_type'=>'fund_allocation',
      //'state'=>$this->check_preview($type)?4:0,
      //'filecrypt'=>$filecrypt,
      'state'=>4,
      'del'=>0
    );

    load("m/file_m")->add($file);

    //生成附件
    $pdf_path = '../app/cache/notice_of_income_distribution/pdf/'.$file_uuid."/";
    if(!file_exists($pdf_path)){
      b2mkdir($pdf_path); //创建文件夹
    }
    $word_path = "../upload/files/".$sid."/".date("Ym")."/".$file_uuid."/".$file_uuid;
    

    $file_uuid = [
      "preview_file_uuid"=>$file_uuid,
      "annex_uuid"=>$this->convert_PDF($word_path,$pdf_path,$new_name),
    ];
    return $file_uuid;
  }


  /*  将word转化为PDF 并且发送邮件
      接收的参数为
      1、word文件的路径
      2、PDF的存放路径
      3、PDF的文件名
  */
  function convert_PDF($word_path,$dir,$title){
    $new_file_origin_path_pdf = $dir.$title;
    $cmd_convertdoc2pdf = "unoconv -f pdf -T 10 -o $new_file_origin_path_pdf $word_path";
    exec("$cmd_convertdoc2pdf");

    $PDF_path = $new_file_origin_path_pdf.'.pdf';
    // 改变PDF文件的权限
    chmod('777',$PDF_path);
    return $PDF_path;
  }

  //发送邮件
  function send_mail() {
    $sid = WEBSID;
    $elem = $this->em->get($_POST['uuid']);
    $elem['data'] = _decode($elem['data']);

    $role_irallocation = $this->cm->db->query("select `uuid` from `config` where `type`='role' and `sid`='$sid' and `key`='role_irallocation '")[0]['uuid'];

    $this->space_usr_m = load("m/space_usr_m");
    $uid = UID;
    $role = $this->space_usr_m->db->query("select * from `space_usr` where `del`=0 and `sid`='$sid' and `uid`='$uid'")[0];
    $role['data'] = _decode($role['data']);

    if(!$role_irallocation || ($role_irallocation && !in_array($role_irallocation,$role['data']['role']))) {
      $param = [
        'code' => 0,
        'tip' => '只有IR才可以发送邮件',
      ];
      ajax_return($param);
    }elseif($elem['data']['cwconfirm'] != '已确认') {
      $param = [
        'code' => 0,
        'tip' => '财务未确认',
      ];
      ajax_return($param);
    }elseif($elem['data']['irconfirm'] != '已确认') {
      $param = [
        'code' => 0,
        'tip' => 'IR未确认',
      ];
      ajax_return($param);
    }else {
      $annex_uuid = explode("/",$elem['data']['annex_uuid']);
      
      $result_email=load('m/queue_m')->add([
        'uid' => UID,
        'sid' => WEBSID,
        'type' => 'email',
        'send_time' => time(),
        'msgid' =>  '',
        'related_id' => '',
        'related_type' => '',
        'info' => _encode([
          'to' => $elem['data']['mailbox'],
          'subject' => $elem['data']['emailtitle'],
          'body' => $this->mail_template(),
          'file' => $elem['data']['annex_uuid'] ?: '',//"../upload/files/hjle1vac7i/202311/hjmjvfi4o4/hjmjvfi4o4",//$PDF_path ?: '',
          'attachment_name' => $annex_uuid[count($annex_uuid)-1] ?: '',
          'partnership' => $elem['data']['partnership'],
          'lpinvestor' => $elem['data']['lpinvestor'],
          'entity_uuid' => $_POST['uuid'],
          'vendor' => 'dalton',
          'func' => 'income_distribution_check_succeeded',
        ])

      ]);

      $elem['data']['sendstate'] = "已发送";
      $this->em->replace($elem , $elem['type'], false, false, false , false);
      $param = [
        'code' => 1,
      ];
      ajax_return($param);
    }


  }

  //邮件模板
  function mail_template(){
    $sm = load('m/space_m');
    $param['logo'] = $_SERVER['HTTP_ORIGIN']."/".$sm->get_setting('logo_mail');
    $mail_body = view('../app_customer/dalton/v/mail/notice_of_income_distribution', $param, true);
    return $mail_body;
  }

  // 收益分配通知书发送成功与否的抓取
  function income_distribution_check_succeeded($result) {
    $msg_m = load('m/msg_m');
    $queue_m = load('m/queue_m');
    $entity_m = load('m/entity_m');
    $space_usr_m = load('m/space_usr_m');

    $param = $queue_m->get($result['mail_uuid']);
    $param['info'] = _decode($param['info']);

    $entity_elem = $this->em->get($result['mail_info']['entity_uuid']);
    $entity_elem['data'] = _decode($entity_elem['data']);

    //$man = array_unique(array_merge($space_usr_m->get_by_role('role_irallocation','uid'),$space_usr_m->get_by_role('Admin','uid')));
    $man = array_unique(array_merge($space_usr_m->get_by_role('role_irallocation','uid')));
    if($result['status'] == 'fail'){
      $msg = "系统提醒:【" . $param['info']['partnership'] . "—" . $param['info']['lpinvestor'] . "】" . "分配通知书邮件发送失败，请留意！";
      $elem = array(
        'eid' => uuid(),
        'type' => 'dept_email',
        'send_time' => time(),
        'content' => $msg,
      );
      for($i = 0; $i < count($man); $i++){
        $uuid = $msg_m->add($man[$i], $elem);
        $msg_m->adddb($uuid);
      }

      $entity_elem['data']['sendstate'] = '发送失败';
    }elseif($result['status'] == 'success'){
      $msg = "系统提醒:【" . $param['info']['partnership'] . "—" . $param['info']['lpinvestor'] . "】" . "分配通知书邮件发送成功！";
      $elem = array(
        'eid' => uuid(),
        'type' => 'dept_email',
        'send_time' => time(),
        'content' => $msg,
      );
      for($i = 0; $i < count($man); $i++){
        $uuid = $msg_m->add($man[$i], $elem);
        $msg_m->adddb($uuid);
      }
      $entity_elem['data']['sendstate'] = '发送成功';
    } 

    $this->em->replace($entity_elem , $entity_elem['type'], false, false, false , false);
  }
  //项目交易交割流程完成后，发送信息，提醒负责人检查更新回购日期、side letter日期、warrants日期
  function rongzi_tixing_msg($flow,$data){
    //dump($flow);

    $sid = WEBSID;
    $entity = $data['form_data']['company_label'];

    $notify_to = $data['form_data']['team_manager'];
    $msg_data['send_time']=time();
    $msg_data['sid']=$sid;
    $msg_data['content']= '【'.$entity.'】新增一轮融资，如需修改回购日期、side letter日期、warrants日期，请及时修改！';
    // dump($msg_data['content']);
    load('m/msg_m')
      ->set_msg_vendor(['msg'])
      ->set_force_mode(true)
      ->batch($notify_to, "add", $msg_data, true);
  }
  
  /**
   * @description:  跟投调研流程-员工跟投意向环节，截止时间投票自动通过 https://oa.vc800.com/?/flow/view/hmyw9m8isf
   * @author: 蒋震 <zjiang@pepm.com.cn>
   * @version: release/v6.6
   * task 2024-01998
   * 定制流程钩子，用于向queue表添加监控流程自动通过
   */
  function flow_yggtdy_atuo_update_start($p,$flow) {
    $sid = WEBSID;
    $related_id = $flow['fentity']['uuid'];
    $queue_m = load("m/queue_m");
    $queue = $queue_m->get(" and `sid`='$sid' and `type`='flow_yggtdy_monitor' and `status`='pending' and `related_id`='$related_id'")[0];
    
    $queue_el = array(
      'uid'          => UID,
      'sid'          => WEBSID,
      'type'         => 'flow_yggtdy_monitor',
      'msgid'        => '',
      'send_time'    => strtotime($p['data']['gtyxsj_end_time']),
      'status'       => 'pending',
      'related_id'   => $flow['fentity']['uuid'],
      'related_type' => $flow['fentity']['type'],
      'info'         => array(
        'pre_data' => $p['data'],
      )
    );
    if (!$queue) {
      $queue_m->add($queue_el);
    }else {
      $queue_m->update($queue['id'], $queue_el);
    }
  }
  
  //用于定时执行每半点和整点多一分钟的时候运行 自动通过逻辑
  function flow_yggtdy_service() {
    $sid = WEBSID;
    $queue_m = load("m/queue_m");
    $time = time();
    $queue = $queue_m->get(" and `sid`='$sid' and `type`='flow_yggtdy_monitor' and `status`='pending' and `send_time`<$time");
    $flow_uuid = [];
    $queue_arr = [];
    foreach ($queue as $k => $v) {
      $flow_uuid[] = $v['related_id'];
      $queue_arr[$v['related_id']] = $v['id'];
    }
    
    $file_uuid = "('" . implode(',', $flow_uuid ). "')";
    $file_uuid = str_replace(",", "','", $file_uuid);
    
    $flow_yggtdy = $this->em->db->query("select * from `entity` where `del`=0 and `sid`='$sid' and `type`='flow_yggtdy' and `uuid` in $file_uuid");
    
    if($flow_yggtdy) {
      $usr_m = load("m/usr_m");
      $msg_m = load('m/msg_m');
      $config = $this->cm->key("keysflow")['pe'];
      $usr = $usr_m->db->query("select `uuid`,`name` from `usr` where `sid`='$sid'");
      $user = [];
      foreach($usr as $k => $v) {
        $user[$v['uuid']] = $v['name'];
      }
    }
    
    foreach($flow_yggtdy as $k => $v) {
      $uuid = $v['uuid'];
      $flow_yggtdy[$k]['data'] = $v['data'] = _decode($v['data']);
      if($v['step'] == 'close') {
        continue;
      }
  
      foreach ($v['data'] as $k1 => $v1) {
        if(is_numeric($k1)) {
          if($v1['data']['company_label']) {
            $company = $v1['data']['company_label'];
          }
        }
      }
      
      $post = [
        'eid'=>$v['uuid'],
        'step_uuid'=>uuid(),
        'step'=>$config[3],
        'name'=>$v['data']['flow']['item'][$config[3]]['name'],
        'action'=>'commit',
        'reject_to'=>'',
        'transfer_to'=>'',
        'reject_to_reason'=>'',
        'disapprove_reason'=>'',
        'addon_reason'=>'',
        'data'=>[],
        'pre_config'=>$v['data']['flow'],
        'form_key' => "form_yggtdytp_new",
      ];
      $time_arr = [];
      if(!$v['data']['flow']['item'][$config[3]]['act_approver']) {
        $v['data']['flow']['item'][$config[3]]['act_approver'] = [];
      }
      $diff = array_diff($v['data']['flow']['item'][$config[3]]['act_approver_all'],$v['data']['flow']['item'][$config[3]]['act_approver']);
      if(!$diff) {
        $queue_m->update($queue_arr[$uuid], array('status'=>'sent'));
      }
      foreach ($diff as $k1 => $v1) {
        $post['step_uuid'] = uuid();
        $post['data']['corer'] = $v1;
        $post['data']['corer_label'] = $user[$v1];
        $post['uid'] = $v1;
        
        if(!in_array(time(),$time_arr)) {
          $time_arr[] = time();
          $post['post_time'] = time();
        }else {
          $post['post_time'] = time()+count($time_arr);
          $time_arr[] = $post['post_time'];
        }
        $post['view_list'] = [$v1=> date("Y-m-d H:i",$post['post_time'])];
        $arr = $flow_yggtdy[$k]['data'];
        $flow_yggtdy[$k]['data'] = [$post['post_time']=>$post];
        foreach($arr as $k2 => $v2) {
          $flow_yggtdy[$k]['data'][$k2] = $v2;
        }
  
        $flow_yggtdy[$k]['data']['approver'] = $flow_yggtdy[$k]['approver'] = $flow_yggtdy[$k]['input_people'];
        $flow_yggtdy[$k]['data']['pre_approver'] = $flow_yggtdy[$k]["pre_approver"] = $v['data']['flow']['item'][$config[3]]['act_approver_all'];
        
        $is_update[$k] = $post['post_time'];
  
        $flow_yggtdy[$k]['data']['view_list'][$post['post_time']] = [$v1=> date("Y-m-d H:i",$post['post_time'])];
        
      }
      $is_update[$k] = 1;
      if($is_update[$k]) {
        $flow_yggtdy[$k]['data']['flow']['item'][$config[3]]['act_approver'] = $flow_yggtdy[$k]['data']['flow']['item'][$config[3]]['act_approver_all'];
        $flow_yggtdy[$k]['step'] = $config[4];
        $flow_yggtdy[$k]['data']['step'] = $config[4];
        $flow_yggtdy[$k]['update_date'] = intval($is_update[$k]);
        //$flow_yggtdy[$k]['input_date'] = intval($flow_yggtdy[$k]['input_date']);
        $flow_yggtdy[$k]['del'] = 0;
        $privilege = [
          "list"=>array_unique(array_merge($flow_yggtdy[$k]['data']['pre_approver'],$config['list'])),
          "detail"=>array_unique(array_merge($flow_yggtdy[$k]['data']['pre_approver'],$config['detail'])),
          "delete"=>$config['delete'],
          "approve"=>[$flow_yggtdy[$k]['approver']],
        ];
        $flow_yggtdy[$k]['privilege'] = _encode($privilege);
        $flow_yggtdy[$k]['data']['notify'] = $flow_yggtdy[$k]['approver'];
        $this->em->replace($flow_yggtdy[$k], $flow_yggtdy[$k]['type'], 'update', false, false, false, false);
        $related_id = $flow_yggtdy[$k]['uuid'];
        $queue_m->update($queue_arr[$related_id], array('status'=>'sent'));
        $content = $config['content'];

        $name = $usr_m->get($flow_yggtdy[$k]['data']['input_people'])['name'];
        $name = ['name'=>$name,'company'=>$company];
        $content = t($content,$name);
        $msg = [];
        //消息格式
        $msg['eid'] = $flow_yggtdy[$k]['uuid'];
        $msg['link'] = "?/flow/view/".$flow_yggtdy[$k]['uuid'];
        $msg['tmpl'] = 'link';
        $msg['type'] = 'flow_yggtdy';
        $msg['send_time'] = time();
        $msg['sid'] = $sid;
        $msg['content'] = $content;
        
        //发送消息
        $result_uuid=$msg_m
          ->set_force_mode(true)
          ->set_msg_vendor('msg')
          ->batch($flow_yggtdy[$k]['approver'], "add", $msg, true);
        $uid = $result_uuid[0];
        $elem = $msg_m->get($uid);
        $elem['data'] = _decode($elem['data']);
        $elem['event_status'] = 'open';
        $elem['role'] = 'executor';
        $elem['data']['link_by'] = $elem['data']['link'];
        $elem['next_step'] = $flow_yggtdy[$k]['step'];
        if($usr_m->get($elem['to_id'])) {
          $elem['from_id'] = $elem['to_id'];
        }else {
          $elem['to_id'] = $this->cm->key("structure")['item'][$elem['to_id']]['member'][0];
        }
  
        $elem['data']['cancel_top_flow_msg'] = null;
  
        $msg_m->update($uid,$elem);
  
        $uuid = $flow_yggtdy[$k]['uuid'];
  
        $res = $msg_m->db->query("select * from `msg` where `sid`='$sid' and `eid`='$uuid' and `event_status`='open' and `role`='executor'");
  
        foreach($res as $k => $v) {
          if($v['uuid'] != $uid) {
            $res[$k]['data'] = _decode($res[$k]['data']);
            $res[$k]['event_status'] = 'close';
            $res[$k]['update_date'] = time();
            $res[$k]['is_read'] = 0;
            $msg_m->update($res[$k]['uuid'],$res[$k]);
          }
        }
      }
    }
  }
  
  //会签下一节点用于将数据从queue表中移除
  function flow_yggtdy_data_remove($p,$flow) {
    $sid = WEBSID;
    $related_id = $flow['fentity']['uuid'];
    $queue_m = load("m/queue_m");
    $queue = $queue_m->get(" and `sid`='$sid' and `type`='flow_yggtdy_monitor' and `status`='pending' and `related_id`='$related_id'");
    foreach ($queue as $k => $v) {
      $queue_m->update($queue[$k]['id'], array('status'=>'sent'));
    }
  }
  
  //用于判断流程中自动通过的节点
  function is_auto_update($sid,$eid) {
    $flow = $this->em->get($eid);
    $flow['data'] = _decode($flow['data']);
    $step_uuid = [];
    foreach ($flow['data'] as $k => $v) {
      if(is_numeric($k)) {
        if($v['step'] == 'hklcst426j' && in_array($v['step_uuid'],$_POST['step_uuid'])) {
          if($v['data']['corer']) {
            $step_uuid[] = $v['step_uuid'];
          }
        }
      }
    }
    ajax_return($step_uuid);
  }

  function move_data_file($sid)
  {
    $data_sid = 'gkas42xvt2';
    $sid = 'ggi7uujrxq';

    $fm = load("m/file_m");
    $zhu_lists = [];

    $lists = $fm->db->query("SELECT * from `file` where `doc_type`in('company_post_filling','company','fund') and `sid`='ggi7uujrxq'");

    foreach ($lists as $k => $v) {
      $zhu_lists[$v['uuid']] = $v;
    }

    $lists = $fm->db->query("SELECT * from `file` where `doc_type`in('company_post_filling','company','fund') and `sid`='gkas42xvt2' order by `id` desc limit 6000,1000  ");

    $data_lists = [];

    foreach ($lists as $k => $v) {
      $size = filesize($v['file']);

      if($size!=$v['size']){
        $fm->preview_encrypt($v['uuid']);
        echo $elem['name'].'-'.$v['name'].'附件成功</br>';
        ob_flush(); //关闭缓存
        flush();    //刷新缓存即立即输出了
      }
    }
  }


  /**
  * @description: 道彤基准线&返投管理定时预警 https://oa.vc800.com/?/flow/view/hxkdme6upe
  * @author: 宋龙须 <lxsong@pepm.com.cn>
  * @version: release/v7.2
  * task 2025-01052(基准线填写发送消息)
  */
  function indicators_addmsg($sid){
    $this->em = load('m/entity_m');
    // 获取推送人信息
    $usr_notification = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'usr_notification' ");
    $notify_to = [];
    $notify_to_label = [];
    foreach($usr_notification as $key => $value){
      $value['data'] = _decode($value['data']);
      $fund_fill_warn = explode(',', $value['data']['fund_fill_warn']);
      $fund_fill_warn_label = explode('，',$value['data']['fund_fill_warn_label']);
      foreach ($fund_fill_warn as $k => $v) {
        if (!in_array($v, $notify_to)) {
          $notify_to[] = $v;
          $notify_to_label[] = $fund_fill_warn_label[$k];
        }
      }
    }
    $msg = [];
    $msg['eid'] = '';
    $msg['link'] = '?/fund_indicators';
    $msg['tmpl'] = 'link';
    $msg['type'] = '';
    $msg['send_time'] = time();
    $msg['sid'] = $sid;
    $msg['content'] = '请填写上月各基金实际指标值';
    // 发送消息
    load('m/msg_m')
     ->set_force_mode(true)
     ->set_msg_vendor('msg')
     ->batch($notify_to, "add", $msg, true);
  }
  /**
  * @description: 道彤基准线&返投管理定时预警 https://oa.vc800.com/?/flow/view/hxkdme6upe
  * @author: 宋龙须 <lxsong@pepm.com.cn>
  * @version: release/v7.2
  * task 2025-01052(基准线未达标预警)
  */
  function fund_indicators_admsg($sid){
    $this->em = load('m/entity_m');
    // 获取推送人信息
    $usr_notification = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'usr_notification' ");
    $notify_to = [];
    foreach($usr_notification as $key => $value){
      $value['data'] = _decode($value['data']);
      $fund_fill_warn = explode(',', $value['data']['fund_fill_warn']);
      $fund_fill_warn_label = explode('，',$value['data']['fund_fill_warn_label']);
      foreach ($fund_fill_warn as $k => $v) {
        if (!in_array($v, $notify_to)) {
          $notify_to[] = $v;
        }
      }
    }
    $time = date('Y-m',strtotime('-1 month',time()));
    // 获取上个月 fund_indicators 模块数据
    $fund_indicators = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'fund_indicators' AND `data`->>'$.year_month' = '$time' ");
    foreach($fund_indicators as $key => $value){
      $value['data'] = _decode($value['data']);
      // 获取关联数据 fund_indicators_history 数据
      $fund = $value['data']['fund'];
      $year = $value['data']['year'];
      $fund_history = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'fund_indicators_history' AND `data`->>'$.fund' = '$fund' AND `data`->>'$.year' = '$year' ")[0];
      $fund_history['data'] = _decode($fund_history['data']);
      // 计算
      $value['data']['diff_net_irr'] = $fund_history['data']['net_irr'] - $value['data']['IR_net_irr'];
      $value['data']['diff_gross_irr'] = $fund_history['data']['gross_irr'] - $value['data']['IR_gross_irr'];
      $value['data']['diff_moic'] = $fund_history['data']['moic'] - $value['data']['IR_moic'];
      $value['data']['diff_tvpi'] = $fund_history['data']['tvpi'] - $value['data']['IR_tvpi'];
      $value['data']['diff_dpi'] = $fund_history['data']['dpi'] - $value['data']['IR_dpi'];

      $msgtxt = '';
      if ($value['data']['diff_net_irr'] > 0) {
        $msgtxt .= 'Net IRR指标低于基准线'.$value['data']['diff_net_irr'].'%,';
      }
      if ($value['data']['diff_gross_irr'] > 0) {
        $msgtxt .= 'Gross IRR指标低于基准线'.$value['data']['diff_gross_irr'].'%,';
      }
      if ($value['data']['diff_moic'] > 0) {
        $msgtxt .= 'MOIC指标低于基准线'.$value['data']['diff_moic'].',';
      }
      if ($value['data']['diff_tvpi'] > 0) {
        $msgtxt .= 'TVPI指标低于基准线'.$value['data']['diff_tvpi'].',';
      }
      if ($value['data']['diff_dpi'] > 0) {
        $msgtxt .= 'DPI指标低于基准线'.$value['data']['diff_dpi'].',';
      }
      $this->em->replace_bare($value);
      if ($msgtxt) {
        $date = date('Y年m月d日');
        $msgtxt = '截止'.$date.','.$value['data']['fund_label'].'的'.$msgtxt.'请知悉。';
        $msg = [];
        $msg['eid'] = $fund_history['uuid'];
        $msg['link'] = '?/fund_indicators_history/view/about/'.$fund_history['uuid'];
        $msg['tmpl'] = 'link';
        $msg['type'] = 'fund_indicators_history';
        $msg['send_time'] = time();
        $msg['sid'] = $sid;
        $msg['content'] = $msgtxt;
        // 发送消息
        load('m/msg_m')
         ->set_force_mode(true)
         ->set_msg_vendor('msg')
         ->batch($notify_to, "add", $msg, true);
      }
    }
  }
  /**
  * 获取流程内关联数据项目负责人数据
  */
  function flow_getmanage(){
    $sid = WEBSID;
    $stepuuid = $_POST['step_uuid'];
    if (!$stepuuid) {
      return false;
    }
    // 获取关联数据信息
    $mubiao_flow = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'mubiao_flow' AND `_rel` = '$stepuuid' ");
    $manage = [];
    foreach ($mubiao_flow as $key => $value) {
      $datax = _decode($value['data']);
      if (!in_array($datax['team_manager'], $manage)) {
        $manage[] = $datax['team_manager'];
      }
    }
    return ajax_return($manage);
  }
  /**
  * @description: 道彤基准线&返投管理定时预警 https://oa.vc800.com/?/flow/view/hxkdme6upe
  * @author: 宋龙须 <lxsong@pepm.com.cn>
  * @version: release/v7.2
  * task 2025-01052 (返投项目未达标提醒)
  */
  function mubiao_admsg($sid){
    // 获取mubiao_gk数据
    $mubiao = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'mubiao_gk' ");
    foreach ($mubiao as $k => $v) {
      $datax = _decode($v['data']);
      $msgname = '';
      $indicators_count = $datax['indicators_count'];
      $notify_to = $datax['IR'];
      if ($indicators_count <= 0) {
        continue;
      }
      for ($i=1; $i <= $indicators_count; $i++) {
        $num = sprintf("%02d",$i);
        $logic = $datax['logic_'.$num];
        $value = $datax['value_'.$num];
        $real = $datax['real_'.$num];
        if (in_array($logic,['是','否'])) {
          $cmp_res = true;
          if ($logic != $real) {
            $cmp_res = false;
          }
        }else{
          $cmp_res = cmp($real, $value, $logic);
        }
        
        if (!$cmp_res) {
          $msgname .= "【".$datax['name_'.$num]."】、";
        }
      }
      if ($msgname) {
        $msgname = rtrim($msgname,'、');
        $msgname = '返投项目【'.$datax['company_label'].'】的'.$msgname.'等指标尚未达成，请知悉。';
        $msg = [];
        $msg['eid'] = $v['uuid'];
        $msg['link'] = '?/fantoumubiao/view/about/'.$v['uuid'];
        // $msg['link'] = '?/mubiao_gk/view/about/'.$value['uuid'];
        $msg['tmpl'] = 'link';
        $msg['type'] = 'mubiao_gk';
        $msg['send_time'] = time();
        $msg['sid'] = $sid;
        $msg['content'] = $msgname;
        // 发送消息
        load('m/msg_m')
         ->set_force_mode(true)
         ->set_msg_vendor('msg')
         ->batch($notify_to, "add", $msg, true);
      }
    }
  }


  /**
  * @description: 道彤-项目后续轮融资到期预警(定时脚本批量操作)
  * @author: 宋龙须 <lxsong@pepm.com.cn>
  * @version:   release/v7.2
  * task 2024-18070
  */
  function view_ytxm_all_cal($sid){
    $company = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'company' AND `data`->>'$.company_state' = '已投' ");
    foreach ($company as $key => $value) {
      $value['data'] = _decode($value['data']);
      $uuid = $value['uuid'];
      // 获取financing内最近日期
      $ic_date = $this->em->db->query(" SELECT `data`->>'$.ic_date' AS ic_date FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'financing' AND `data`->>'$.company' = '$uuid' AND `data`->>'$.state' IN ('投资交割','投后管理','退出交割') ORDER BY UNIX_TIMESTAMP(`data`->>'$.ic_date') DESC LIMIT 0,1 ")[0]['ic_date'];
      $value['data']['inv_date'] = $ic_date;
      // 获取financing内所有的交易轮次
      $rp_round = $this->em->db->query(" SELECT GROUP_CONCAT(`data`->>'$.round') AS round , GROUP_CONCAT(`data`->>'$.round_label') AS round_label FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'financing' AND `data`->>'$.company' = '$uuid' AND `data`->>'$.state' IN ('投资交割','投后管理','退出交割') ")[0];
      $value['data']['rp_round'] = '';
      $value['data']['rp_round_label'] = '';
      if ($rp_round) {
        $value['data']['rp_round'] = $rp_round['round'];
        $value['data']['rp_round_label'] = $rp_round['round_label'];
      }
      // 获取未参与轮次
      $gontzjtogp = $this->em->db->query(" SELECT GROUP_CONCAT(`data`->>'$.gontzjtogp') AS gontzjtogp FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'followround' AND `data`->>'$.company' = '$uuid' ")[0];
      $gontzjtogphis = $value['data']['gontzjtogp'];
      if ($gontzjtogp) {
        $value['data']['gontzjtogp'] = implode(',', array_unique(explode(',',$gontzjtogp['gontzjtogp'])));
      }else{
        $value['data']['gontzjtogp'] = '';
      }
      if ($value['data']['gontzjtogp'] != $gontzjtogphis || !$value['data']['gontzjtogp_uptime'] ) {
        $value['data']['gontzjtogp_uptime'] = time();
      }
      $this->em->replace($value,$value['type'],'add', false, false, false, false);
    }
  }
  /**
  * @description: 道彤-项目后续轮融资到期预警(update脚本)
  * @author: 宋龙须 <lxsong@pepm.com.cn>
  * @version:   release/v7.2
  * task 2024-18070
  */
  function company_view_ytxm_cal($eid){
    $sid = WEBSID;
    $elem = $this->em->get_one($eid);
    $elem['data'] = _decode($elem['data']);
    $uuid = $eid;
    // 获取financing内最近日期
    $ic_date = $this->em->db->query(" SELECT `data`->>'$.ic_date' AS ic_date FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'financing' AND `data`->>'$.company' = '$uuid' AND `data`->>'$.state' IN ('投资交割','投后管理','退出交割') ORDER BY UNIX_TIMESTAMP(`data`->>'$.ic_date') DESC LIMIT 0,1 ")[0]['ic_date'];
    $elem['data']['inv_date'] = $ic_date;
    // 获取financing内所有的交易轮次
    $rp_round = $this->em->db->query(" SELECT GROUP_CONCAT(`data`->>'$.round') AS round , GROUP_CONCAT(`data`->>'$.round_label') AS round_label FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'financing' AND `data`->>'$.company' = '$uuid' AND `data`->>'$.state' IN ('投资交割','投后管理','退出交割') ")[0];
    $elem['data']['rp_round'] = '';
    $elem['data']['rp_round_label'] = '';
    if ($rp_round) {
      $elem['data']['rp_round'] = $rp_round['round'];
      $elem['data']['rp_round_label'] = $rp_round['round_label'];
    }
    // 获取未参与轮次
    $gontzjtogp = $this->em->db->query(" SELECT GROUP_CONCAT(`data`->>'$.gontzjtogp') AS gontzjtogp FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'followround' AND `data`->>'$.company' = '$uuid' ")[0];
    $gontzjtogphis = $elem['data']['gontzjtogp'];
    if ($gontzjtogp) {
      $elem['data']['gontzjtogp'] = implode(',', array_unique(explode(',',$gontzjtogp['gontzjtogp'])));
    }else{
      $elem['data']['gontzjtogp'] = '';
    }
    if ($elem['data']['gontzjtogp'] != $gontzjtogphis || !$elem['data']['gontzjtogp_uptime'] ) {
      $elem['data']['gontzjtogp_uptime'] = time();
    }
    $this->em->replace($elem,$elem['type'],'add', false, false, false, false);
  }

  /**
  * @description: 道彤-项目后续轮融资到期预警(定时脚本发送预警信息)
  * @author: 宋龙须 <lxsong@pepm.com.cn>
  * @version:   release/v7.2
  * task 2024-18070
  */
  function warning_cal($sid){
    // 获取company信息
    $company = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'company' AND `data`->>'$.company_state' = '已投' ");
    // 获取预警人员信息
    $usr_notification = $this->em->db->query(" SELECT * FROM `entity` WHERE `del` = 0 AND `sid` = '$sid' AND `type` = 'usr_notification' ");
    $notify_to = [];
    $notify_to_label = [];
    foreach ($usr_notification as $key => $value) {
      $value['data'] = _decode($value['data']);
      $maturity_warn = explode(',',$value['data']['maturity_warn']);
      $maturity_warn_label = explode('，',$value['data']['maturity_warn_label']);
      foreach ($maturity_warn as $k => $v) {
        if (!in_array($v, $notify_to)) {
          $notify_to[] = $v;
          $notify_to_label[] = $maturity_warn_label[$k] ;
        }
      }
    }
    foreach ($company as $key => $value) {
      $value['data'] = _decode($value['data']);
      $rp_round = explode(',', $value['data']['rp_round']);
      $msg = [];
      $date = '';
      // A.(新投项目) 字段'参投轮次'(key: rp_round)中只有一个轮次 且 字段'不参与轮次'(key: gontzjtogp)为空; 以这条数据中的字段'最近投资日期'(key: inv_date)为准, 每天触发脚本, 如果发现当前系统日期已超过该'交易日期'达18个月, 则触发预警
      if (count($rp_round) == 1 && empty($value['data']['gontzjtogp']) && $value['data']['inv_date'] ) {
        $inv_date = $value['data']['inv_date'];
        // 获取18个月后的日期
        $endtdate = strtotime($inv_date)+18*30*86400;
        if (time() >= $endtdate ) {
          $msg = $value;
          $date = 18;
          if ($notify_to) {
            $this->message_push($msg,$sid,$date,$notify_to,$notify_to_label);
          }
        }
      }
      if ( $value['data']['inv_date'] && $value['data']['gontzjtogp_uptime'] ) {
        // 如果发现当前系统日期已超过该'最近投资日期'达24个月 且 该已投项目的字段'不参与轮次'(key: gontzjtogp)在这24个月内并未发生变动, 则触发预警
        $inv_date = $value['data']['inv_date'];
        // 获取24个月后的日期
        $endtdate = strtotime($inv_date)+24*30*86400;
        $enduptime = $value['data']['gontzjtogp_uptime']+24*30*86400;
        if (time()>=$endtdate && time() >= $enduptime) {
          $msg = $value;
          $date = 24;
          if ($notify_to) {
            $this->message_push($msg,$sid,$date,$notify_to,$notify_to_label);
          }
        }
      }
    }
  }
  /**
  * @description: 道彤-项目后续轮融资到期预警(发送预警信息/保存发送预警历史信息)
  * @author: 宋龙须 <lxsong@pepm.com.cn>
  * @version:   release/v7.2
  * task 2024-18070
  */
  function message_push($elem,$sid,$date,$notify_to,$notify_to_label){
    // 设置推送信息
    $msg = [];
    $msg['eid'] = $elem['uuid'];
    $msg['link'] = '';
    if ($elem['uuid']) {
      $msg['link'] = '?/view_ytxm/view/about/'.$elem['uuid'];
    }
    $msg['tmpl'] = 'link';
    $msg['type'] = $elem['type'];
    $msg['send_time'] = time();
    $msg['sid'] = $sid;
    $msg['content'] = $elem['name'].'项目自'.date('Y年m月d日').'交割后'.$date.'个月无后续轮，请知悉!';
    //发送消息
    load('m/msg_m')
     ->set_force_mode(true)
     ->set_msg_vendor('msg')
     ->batch($notify_to, "add", $msg, true);
    // 发送预警信息历史记录
    $history = [];
    // 项目名称
    $history['company'] = $elem['uuid'];
    $history['company_label'] = $elem['name'];
    // 所属基金
    $history['fund'] = $elem['data']['fund'];
    $history['fund_label'] = $elem['data']['fund_label'];
    $history['fund_optgroup'] = $elem['data']['fund_optgroup'];
    // 投后负责人
    $history['touhou_manager'] = $elem['data']['touhou_manager'];
    $history['touhou_manager_label'] = $elem['data']['touhou_manager_label'];
    $history['touhou_manager_optgroup'] = $elem['data']['touhou_manager_optgroup'];
    // 触发内容
    $history['hwum87ggfp'] = $elem['name'].'项目交割后'.$date.'个月无后续轮';
    // 项目交割时间
    $history['inv_date'] = $elem['data']['inv_date'];
    // 时间跨度
    if ($date == 18) {
      $history['hwum8w1pmi'] = '18个月';
    }elseif ($data == 24) {
      $history['hwum8w1pmi'] = '两年';
    }
    // 触发时间
    $history['hwum9317u7'] = date('Y-m-d H:i:s',time());
    // 通知人员
    $history['hwum97n71n'] = implode(',', $notify_to);
    $history['hwum97n71n_label'] = implode('，', $notify_to_label);
    $add_history['uuid'] = uuid();
    $add_history['data'] = $history;
    $add_history['type'] = 'financing_warning_history';
    $this->em->replace($add_history,$add_history['type'],'add', false, false, false, false);
  }  

}