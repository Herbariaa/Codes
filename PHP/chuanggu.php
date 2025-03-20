<?php
/**
 * 创谷资本
 */
class chuanggu extends server_script
{
  function __construct()
  {
    $this->em = load("m/entity_m");
    $this->msg_m = load("m/msg_m");
    $this->cm = load("m/config_m");
    $this->space_usr_m = load("m/space_usr_m");
    $this->sid = WEBSID;
  }

    /**
     * task/2021-08312 汇总项目现金流到投资组合
     * @param string $fundUUID fund uuid
     * @param string $companyUUID company uuid
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function summaryCashflow($fundUUID, $companyUUID)
    {
        if (!$fundUUID || !$companyUUID) {
            return false;
        }

        // 投资组合
        $portfolio = $this->em->get("AND `del` = 0 AND `type` = 'portfolio' AND `data`->>'$.fund' = '$fundUUID' AND `data`->>'$.company' = '$companyUUID'")[0];
        // 项目现金流
        $cashflowList = $this->em->get("AND `del` = 0 AND `type` = 'cashflow_portfolio' AND `data`->>'$.fund' = '$fundUUID' AND `data`->>'$.company' = '$companyUUID'  order by `data`->>'$.fukuanshijian' ASC");

        if (!$cashflowList) {
            return false;
        }

        // 整理现金流数据
        $list = array_fill_keys([
            'inv_money',    // 累计投资金额
            'exit_money',   // 累计分红金额
            'gwnpox1924',   // 本轮投资金额
            'frj14z2u6f',   // 本轮投资日期
            'gwr7wdtxrs',   // 本轮投资占比
        ], 0);
        foreach ($cashflowList as $cashflow) {
            $data = _decode($cashflow['data']);
            switch ($data['cf_type']) {
                case '(流出)投资款':
                    $list['inv_money'] += $data['fukuanjine'];
                    // 记录最新投资金额和日期
                    $list['frj14z2u6f'] = $data['fukuanshijian'];
                    $list['gwnpox1924'] = $data['fukuanjine'];
                    $list['gwr7wdtxrs'] = $data['gwr7wdtxrs'];
                    break;
                case '分红款':
                    $list['exit_money'] += $data['fukuanjine'];
            }
        }

        if (!$portfolio) {
            $elem = [
                'type' => 'portfolio',
                'data' => [
                    'fund' => $data['fund'],            // 基金
                    'fund_label' => $data['fund_label'],
                    'company' => $data['company'],            // 基金
                    'company_label' => $data['company_label'],
                ],
            ];
            foreach ($list as $field => $value) {
                $elem['data'][$field] = $value;
            }

            $this->em->replace($elem, $elem['type'], 'add', false, false, false);
            return true;
        }

        $portfolio['data'] = _decode($portfolio['data']);
        foreach ($list as $field => $value) {
            $portfolio['data'][$field] = $value;
        }

        $this->em->replace_bare($portfolio);
    }

    /**
     * task/2022-02002 获取投资比例
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.2
     */
    public function getRatio()
    {
        $fundUUID = $_POST['fund'] ?? false;
        $companyUUID = $_POST['company'] ?? false;
        if (!$fundUUID || !$companyUUID) {
            ajax_return(['ok' => 0, 'msg' => '参数错误']);
        }

        $cashflow = $this->em->get("AND `del` = 0
            AND `type` = 'cashflow_portfolio'
            AND `data`->>'$.fund' = '$fundUUID'
            AND `data`->>'$.company' = '$companyUUID'
            ORDER BY `data`->>'$.fukuanshijian' DESC
        ")[0];
        if (!$cashflow) {
            ajax_return(['ok' => 0, 'msg' => '未找到记录']);
        }

        $data = _decode($cashflow['data']);
        ajax_return(['ok' => 1, 'ratio' => $data['gwr7wdtxrs']]);
    }

    /**
     * task/2021-08312 新增投资组合后同步创建项目现金流
     * @param string $eid portfolio uuid
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function copyCashflowByPortfolio($eid)
    {
        if (!$eid) {
            return false;
        }
        $portfolio = $this->em->get_one($eid);
        if (!$portfolio) {
            return false;
        }
        $data = _decode($portfolio['data']);
        // 无 投资金额 不创建 现金流
        if (!$data['gwnpox1924']) {
            return false;
        }

        $list = [
            'fund'          => 'fund',         // 基金
            'company'       => 'company',      // 项目
            'fukuanshijian' => 'frj14z2u6f',   // 实际付款时间
            'fukuanjine'    => 'gwnpox1924',   // 金额
        ];
        $elem = ['type' => 'cashflow_portfolio', 'data' => [],];
        foreach ($list as $key => $field) {
            $elem['data'][$key] = $data[$field];
            if (isset($data[$field . '_label'])) {
                $elem['data'][$key . '_label'] = $data[$field . '_label'];
            }
        }
        $elem['data']['cf_type'] = $elem['data']['cf_type_label'] = '(流出)投资款';

        $this->em->add($elem);
    }

    /**
     * task/2021-08312 汇总投资人现金流到投资人数据
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function summaryCashflowCustomer($fundUUID, $customerUUID)
    {
        if (!$fundUUID || !$customerUUID) {
            return false;
        }

        // 投资组合
        $customer = $this->em->get("AND `del` = 0 AND `type` = 'efrom' AND `data`->>'$.fund' = '$fundUUID' AND `data`->>'$.eeee' = '$customerUUID'")[0];
        // 项目现金流
        $cashflowList = $this->em->get("AND `del` = 0 AND `type` = 'cashflow_customer' AND `data`->>'$.fullname' = '$fundUUID' AND `data`->>'$.customer' = '$customerUUID'  order by `data`->>'$.date' ASC");
        if (!$cashflowList) {
            return false;
        }
        // 整理现金流数据
        $list = array_fill_keys([
            'inv_money',    // 实缴金额
            'exit_money',   // 累计分配
            'interest',     // 累计分红
        ], 0);
        foreach ($cashflowList as $cashflow) {
            $data = _decode($cashflow['data']);
            switch ($data['cf_type']) {
                case '(流入)合伙人出资':
                    $list['inv_money'] += $data['amount'];
                    break;
                case '(流出)分配收益':
                    $list['interest'] += $data['amount'];
                    break;
                case '(流出)分配本金':
                    $list['exit_money'] += $data['amount'];
                    break;
            }
        }

        if (!$customer) {
            $elem = [
                'type' => 'efrom',
                'data' => [
                    'fund' => $data['fullname'],            // 基金
                    'fund_label' => $data['fullname_label'],
                    'eeee' => $data['customer'],            // 基金
                    'eeee_label' => $data['customer_label'],
                ],
            ];
            foreach ($list as $field => $value) {
                $elem['data'][$field] = $value;
            }

            $this->em->add($elem);
            return true;
        }

        $customer['data'] = _decode($customer['data']);
        foreach ($list as $field => $value) {
            $customer['data'][$field] = $value;
        }

        $this->em->replace_bare($customer);

    }

    /**
     * task/2022-02000 年度任务 金额汇总计算
     * @param string $fundUUID fund uuid
     * @param string $date 年份 eg： 2022年
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.2
     */
    public function summaryYearTask($fundUUID, $year, $fromFund = false)
    {
        if (!$fundUUID || !$year) {
            return false;
        }
        if (!$fromFund) {
            // 获取年份
            $year = date("Y", strtotime($year)) . '年';
        }

        // 获取年度任务，没有进行创建
        $task = $this->em->get("AND `del` = 0
            AND `type` = 'jjndtzrw'
            AND `data`->>'$.name' = '$fundUUID'
            AND `data`->>'$.gws9s6nf48' = '$year'
        ")[0];
        if (!$task) {
            $fund = $this->em->get_one($fundUUID);
            $task = [
                'type' => 'jjndtzrw',
                'data' => [
                    'name' => $fundUUID,            // 基金
                    'name_label' => $fund['name'],
                    'gws9s6nf48' => $year,      // 时间
                    'gtcqdnuryf' => 0,      // 年度已完成任务金额
                    'gws9s639cr' => 0,      // 年度实际提取管理费
                ],
            ];
            $taskUUID = $this->em->add($task);
            $task = $this->em->get_one($taskUUID);
        }
        $task['data'] = _decode($task['data']);
        // 初始化
        $task['data']['gtcqdnuryf'] = $task['data']['gws9s639cr'] = 0;

        // 项目现金流 (流出)投资款
        $cashflowList = $this->em->get("AND `del` = 0
            AND `type` = 'cashflow_portfolio'
            AND `data`->>'$.cf_type' = '(流出)投资款'
            AND `data`->>'$.fund' = '$fundUUID'
        ");
        foreach ($cashflowList as $cashflow) {
            $data = _decode($cashflow['data']);
            // 相同年进行汇总，否则跳过
            $cashflowYear = date("Y", strtotime($data['fukuanshijian'])) . '年';
            if ($cashflowYear !== $year) {
                continue;
            }
            $task['data']['gtcqdnuryf'] += $data['fukuanjine'];     // 年度已完成任务金额
        }

        // 其他现金流出 基金管理费
        $otherList = $this->em->get("AND `del` = 0
            AND `type` = 'cashflow_other_outflow'
            AND `data`->>'$.fullname' = '$fundUUID'
            AND `data`->>'$.cf_type' = '基金管理费'
        ");
        foreach ($otherList as $other) {
            $data = _decode($other['data']);
            // 相同年进行汇总，否则跳过
            $cashflowYear = date("Y", strtotime($data['date'])) . '年';
            if ($cashflowYear !== $year) {
                continue;
            }
            $task['data']['gws9s639cr'] += $data['amount'];     // 年度实际提取管理费
        }
        // 计算公式
        $task['data']['gwj3gfufmt'] = ($task['data']['gtcqdnuryf'] / $task['data']['gtcpx465wu']) * 100;
        $task['data']['gtcqe2uehq'] = $task['data']['gtcpx465wu'] - $task['data']['gtcqdnuryf'];

        $this->em->replace_bare($task);
    }

    /**
     * task/2021-08546 员工评分计算平均分
     * @param array $point
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function preCalculateAverageScore($point)
    {
        $flow        = $this->em->get_one($point['uuid']);
        $flowData    = _decode($flow['data']);
        $flowAllKey  = array_keys($flowData['flow']['item']);  // 流程所有节点
        $retract     = $flowData['retract'];                   // 撤回
        $rejectArray = $flowData['reject_array'];              // 驳回
        $list        = [
            'gx8uhxpfja' => 'gxek8ccsvb',   // 同事评分
            'gwbctuex0z' => 'gxek8dxyn2',   // 分管领导
            'gwbctuexla' => 'zongjingli',   // 总经理
        ];
        $scoreList  = array_fill_keys(array_keys($list), []);
        $scorePoint = array_keys($list);
        foreach ($flowData as $k => $step) {
            if (
                $step['action'] == 'transfer'  // 移交的节点
                || !in_array($step['step'], $flowAllKey)        // 非流程节点
                || in_array($k, $rejectArray)                   // 驳回节点
                || in_array($k, $retract)                       // 撤回节点
                || !in_array($step['step'], $scorePoint)                   // 非评分节点
            ) {
                continue;
            }

            $scoreList[$step['step']][] = $step['data']['gwc6zln8651'];
        }

        // 求均分
        $data = array_fill_keys(array_values($list), 0);
        foreach ($scoreList as $step => $scores) {
            if (!$scores) {
                continue;
            }
            $field  = $list[$step];
            $data[$field] = array_sum($scores) / count($scores);
        }
        // 考核总平均分
        $data['gwkuzo1aga'] = array_sum($data) / count($data);

        return $data;
    }

    // 预约了会议室的日程，同步写入预定会议室模块（meetingroom_reserve）数据
    function cal_mr_sync($eid){
        $em = $this->em;
        if(!$eid) return;

        $elem = $em->get_one($eid);
        if(!$elem) return;

        $elem['data'] = _decode($elem['data']);
        $c2m = [
            "g61mud9ije"        => "ftwbux5ys7", //会议室
            "g61mud9ije_label"  => "ftwbux5ys7_label", //会议室
            "meeting_system"    => "meeting_system",//是否需要zoom
            "date_on"           => "ftwbux5z7m", //时间
            "ftwbux5zd5"        => "ftwbux5zd5", //时长
            "ftwbux5zd5_label"        => "ftwbux5zd5_label", //时长
            "date_off"          => "date_off",
            "remind_time"       => "ftwbux5zgz", //会前时间提醒
            "remind_time_label" => "ftwbux5zgz_label", //会前时间提醒
            "name"              => "ftwbux5ywc",//会议主题
            "content"           => "ftwbux5z04", //会议内容
            "member"            => "ftwbux5zks",//参与人
            "member_label"      => "ftwbux5zks_label",//参与人
        ];

        $type = $elem['type'];
        $key  = "_sync_uuid";
        $uuid = $elem['uuid'];
        if($elem['type'] == 'meetingroom_reserve'){
            $r = $em->get(" AND `del`=0 AND `type`='calendar' AND `data`->>'$.$key'='$uuid'");
            if($r[0]){
                $relem = $r[0];
                $relem['data'] = _decode($relem['data']);
            }else{
                $relem = [
                    "type" => "calendar",
                    "data" => ["_sync_uuid" => $uuid]
                ];
            }
            foreach($c2m as $c=>$m){
                if($elem['data'][$m]){
                    $relem['data'][$c] = $elem['data'][$m];
                }
            }
            //$relem['data']['date_off'] = date('Y-m-d H:i',(strtotime($relem['data']['date_on'])+(($elem['data']['ftwbux5zd5']+1)*15*60)));
            $syncuuid = $em->replace($relem, $relem['type'], "script", false, false);

            if(!$elem['data'][$key]){
                $elem['data'][$key] = $syncuuid;
                $em->replace($elem, $elem['type'], "script", false, false);
            }
        } elseif ($elem['type']=='calendar') {
            $r = $em->get(" AND `type`='meetingroom_reserve' AND `data`->>'$.$key'='$uuid'");
            // 1.没选会议室，如果是编辑，还需要把已有的标删除
            // 2.编辑后重新选了会议室，需要把已删除数据恢复，并更新数据
            if (!$elem['data']['g61mud9ije']) {
                if ($r[0]) {
                    $relem = $r[0];
                    $relem['del'] = 1;
                    $em->replace($relem, $relem['type'], "script", false, false);
                }
                return;
            }

            if($r[0]){
                $relem = $r[0];
                $relem['del'] = 0;
                $relem['data'] = _decode($relem['data']);
            }else{
                $relem = [
                    "type" => "meetingroom_reserve",
                    "data" => ["_sync_uuid" => $uuid]
                ];
            }
            foreach($c2m as $c=>$m){
                $elem['data'][$c] && $relem['data'][$m] = $elem['data'][$c];
            }

            // 计算会议持续时间
            //$duration = ceil((strtotime($elem['data']['date_off'])-strtotime($elem['data']['date_on']))/60/15);
            // 结束时间小于开始时间了，置为0，即默认的15分钟
            /*if ($duration < 1) {
                $duration = 1;
            }*/
            // 预定会议室模块设置最多16小时
            /*if ($duration > 64) {
                $duration = 64;
            }*/
            //$relem['data']['ftwbux5zd5'] = $duration - 1;
            //$relem['data']['ftwbux5zd5_label'] = floor($duration*15/60).":".str_pad(($duration%4)*15, 2, 0);
            $syncuuid = $em->replace($relem, $relem['type'], "script", false, false);

            if (!$elem['data'][$key]) {
                $elem['data'][$key] = $syncuuid;
                $em->replace($elem, $elem['type'], "script", false, false);
            }
        }
    }


    //会议室系统预定时间查重
    function check_system() {
        date_default_timezone_set('Asia/Shanghai');
        $uuid     = $_POST['uuid']; // 当前编辑entity的uuid
        $time     = $_POST['date']; // 日期
        $date_start = $_POST['date_start']; // 日期开始
        $date_end = $_POST['date_end']; // 日期开始
        $room     = $_POST['room']; // 会议室
        $system   = $_POST['system']; // 是否需要Zoom
        $duration = $_POST['duration']; // 时长ftwbux5zd5 0是15分钟
        $calendar = $_POST['calendar']; // 日程中预定标记
        $aj_param = array("ret" => false); // ret: true为冲突
        //dump($_POST);

        if (empty($time) || empty($room)) {
            $aj_param['ret'] = false;
            ajax_return($aj_param);
        }

        // 获取预定记录uuid
        $em = load('m/entity_m');
        if ($calendar) {
            $cal = $em->get_one($uuid);
            $cal_data = _decode($cal['data']);
            $mr_uuid = $cal_data['_sync_uuid'];
        } else {
            $mr_uuid = $uuid;
        }

        $room_key = 'ftwbux5ys7'; // 会议室
        $time_key = 'ftwbux5z7m'; // 会议时间
        $time_end_key = 'date_off'; // 会议结束时间
        $duration_key = 'ftwbux5zd5'; // 时长

        // data.meeting_system 是否需要Zoom
        $sql = " AND del='0' AND type='meetingroom_reserve'";
        // 勾选了zoom，任意条件成立则冲突
        if ($system === '是') {
            $sql .= " AND (`data`->>'$.${room_key}'='$room' OR `data`->>'$.meeting_system'='$system')";
        } else {
            $sql .= " AND `data`->>'$.${room_key}'='$room'";
        }

        // 注意：未按工作时间计算，因为不确定上下班时间来处理当天剩余时长。
        // 当前提交数据结束时间
        $time_start = strtotime($time);
        //$time_end = $time_start + (($duration + 1) * 15 * 60);
        $time_end = strtotime($date_end);
        // 换算查询数据结束时间
        $sql_timestamp = "UNIX_TIMESTAMP(`data`->>'$.${time_key}')";
        //$sql_end = "($sql_timestamp + ((`data`->>'$.${duration_key}'+1)*15*60))";
        $sql_end = "UNIX_TIMESTAMP(`data`->>'$.${time_end_key}')";

        $sql .= " AND (";
        $sql .= " ($sql_timestamp > '$time_start' AND $sql_timestamp < '$time_end')"; // 开始时间在区间内
        $sql .= " OR ($sql_end > '$time_start' AND $sql_end < '$time_end')"; // 结束时间在区间内
        $sql .= " OR (($sql_timestamp <= '$time_start') AND ($sql_end >= '$time_end'))"; // 区间外，但是包含关系
        $sql .= ")";

        $em->db->query('SET time_zone = "+08:00";');
        $info = $em->get($sql);
        if (!empty($info) && $info[0]['uuid'] !== $mr_uuid) {
            $aj_param['ret'] = true;
            ajax_return($aj_param);
        }

        $aj_param['ret'] = false;
        ajax_return($aj_param);
    }

    /**
    * @description:  会议室预定删除 会同步删除日程  日程删除会同步删除对应的会议室预定记录
    * @param string $eid 会议室预定 或者 日程的uuid 两个通道公用
    * @return  无返回值
    * @author: Vodmort <qyf@pepm.com.cn>
    * @version: [v4.11]
    */
    function del_syn($eid){
        if(!$eid) return;

        //根据eid获取对应的type
        $del_item = $this->em->get_one($eid);
        if(!$del_item) return;

        $del_item['data'] = _decode($del_item['data']);
        //获取其type
        $del_type = $del_item['type'];
        //如果是calendar 则获取data中的 _sync_uuid 进行会议室预定的相关删除
        if(in_array($del_type, ['calendar','meetingroom_reserve'])){
            //获取 _sync_uuid 
            $del_sync_uuid = $del_item['data']['_sync_uuid'];
            //检查该数据是否被删除了 如果已经被删除则不需要再次触发del
            $sync_item = $this->em->get_one($del_sync_uuid);
            if($sync_item['del'] == 0){
            $this->em->del($del_sync_uuid);
            }
        }
        }

    /**
     * task/2022-00021 预警批量处理 每日执行一次
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function timeCompanyMonitor()
    {
        // 获取所有项目依次处理
        $companyList = $this->em->get("AND `del` = 0
            AND `type` = 'company'
            AND `data`->>'$.state' <> '共有资源'
            AND `data`->>'$.state' <> '打款交割'
            AND `data`->>'$.state' <> '退出'
        ");
        $config = load('m/config_m')->key('memo_config');
        foreach ($companyList as $company) {
            $company['data'] = _decode($company['data']);
            // 项目状态变更提醒
            if ($company['data']['state'] !== '投后') {
                $company = $this->monitorCompanyState($company, $config['state_change']);
                continue;
            }
            // 业绩对赌/回购变更提醒
            $company = $this->monitorContractStatus($company, $config['contract_status']);
            // 报告提交频次不为空，发送提醒
            if ($company['data']['gy3ypinx33']) {
                // 投后管理报告提醒
                $company = $this->monitorAfterReport($company, $config['after_report']);
            }
            // 打款交割日期不为空，发送提醒
            if ($company['data']['after_plan_date']) {
                // 投后管理计划提醒
                $company = $this->monitorAfterPlan($company, $config['after_plan']);
            }
        }

        // 处理memo通知
        $this->monitorMemo($config);
    }

    /**
     * task/2022-00021 处理memo通知
     * @param array $config 通知消息模板等配置
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function monitorMemo($config)
    {
        $memos = $this->em->get("AND `del` = 0
            AND `type` = 'memo'
            AND `data`->>'$.fkf94y9c4z' = '项目动态'
            AND `data`->>'$.flcsqifwd8' = '待办'
        ");
        // 未处理的memo每隔7天通知一次
        $now = time();  // 当前日期时间戳
        foreach ($memos as $memo) {
            $memo['data'] = _decode($memo['data']);
            $monitorType = $memo['data']['monitor_type'];
            $lastNotifyDate = $memo['data']['last_notify_date'];    // 上次通知日期
            $deadline = $this->getSecondsByTimeQuantum(7) + strtotime($lastNotifyDate);    // 通知时间
            if ($deadline <= $now) {
                $this->sendMessage($memo['uuid'], $config[$monitorType]['msg']['notify'], $memo['data']);
                $memo['data']['last_notify_date'] = date("Y-m-d", $now);

                $this->em->replace_bare($memo);
            }
        }
    }

    /**
     * task/2022-00021 根据时间段计算相应的秒数
     * @param int $day 天数
     * @param int $month 月
     * @param int $year 年
     * @param int $times 每年提醒频次
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function getSecondsByTimeQuantum($day = 0, $month = 0, $year = 0, $times = 0)
    {
        $baseDay = 86400;
        $baseMonth = 2592000;
        $baseYear = 31104000;
        if ($times) {
            $day = floor(365 / $times);
        }

        return $day * $baseDay + $month * $baseMonth + $year * $baseYear;
    }

    /**
     * task/2022-00021 创建memo数据
     * @param array $company company data
     * @param array $list memo data 替换值
     * @param bool $uploadReport 是否上传报告
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function addMemoByCompany($company, $list = [], $uploadReport = false)
    {
        $needUpload = $uploadReport ? '是' : '否';
        $date = date("Y-m-d", time());
        $memo = [
            'type' => 'memo',
            'data' => [
                'fkoocr75um'               => '【预警】',                                                                     // 标题
                'glian'                    => $company['uuid'],                                                           // 关联
                'glian_label'              => $company['name'],
                'fkf94y9c4z'               => '项目动态',                                                                     // 类型
                'fkf94y9c4z_label'         => '项目动态',                                                                     // 类型
                'flcsqifwi5'               => 'system',                                                                   // 发布人
                'flcsqifwi5_label'         => '系统',
                'fkf94r4fbb'               => $date,                                                                      // 发布日期
                'flcsqifwd8'               => '待办',                                                                       // 状态
                'flcsqifwd8_label'         => '待办',                                                                       // 状态
                'fkf91utdqo'               => '',                                                                         // 事件内容
                'flcsqifwlv'               => $company['data']['team_fzrA'],                                                   // 执行人
                'flcsqifwlv_label'         => $company['data']['team_fzrA_label'],                                          // 执行人
                'last_notify_date'         => $date,                                                                      // 上次提醒日期
                'need_upload_report'       => $needUpload,                                                                // 需要上传报告
                'need_upload_report_label' => $needUpload,
            ],
        ];
        foreach ($list as $field => $value) {
            $memo['data'][$field] = $value;
        }
        // 创建memo
        $memoUUID = $this->em->replace($memo, $memo['type'], 'add', false, false, false);

        return $memoUUID;
    }

    /**
     * task/2022-00021 项目状态变更预警
     * @param $company array company data
     * @param $config array memo_config state_change
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function monitorCompanyState($company, $config)
    {
        $now = time();      // 当前日期时间戳
        $lastNotifyDate = $company['data']['last_notify_date'];             // 上次通知日期
        $notifyTimes = intval($company['data']['notify_times']);            // 通知次数
        // 无上次通知日期，取项目生成日期
        if (!$lastNotifyDate) {
            $lastNotifyDate = $company['data']['last_notify_date'] = $company['data']['f25513'];
        }
        // 计算通知日期
        $deadline = strtotime($lastNotifyDate) + $this->getSecondsByTimeQuantum(0, 1);
        if ($deadline <= $now) {
            // 执行人默认为提交人，项目状态为立项、内核会、投委会时，执行人为 项目经理A角
            switch($company['data']['state']) {
                case '入库':
                case '初筛':
                    $list['flcsqifwlv'] = $company['input_people'];
                    $list['flcsqifwlv_label'] = load('m/space_usr_m')->profile(WEBSID, $company['input_people'])['name'];
                    break;
            }
            // 替换模板变量
            $data = array_merge($company, $company['data']);
            $data = array_merge($data, ['year' => date("Y", $now), 'month' => date("m", $now), 'day' => date("d", $now)]);
            // 提醒/抄送文本
            foreach ($config['memo'] as $field => $tpl) {
                $list[$field] = t($tpl, $data);
            }
            // 预警类型
            $list['monitor_type'] = 'state_change';
            $list['monitor_type_label'] = '项目状态变更';
            // 项目跟进选项
            $list['gyd81v6099'] = $list['gyd81v6099_label'] = '跟进';
            // 更新项目中的上次提醒日期
            $company['data']['last_notify_date'] = date("Y-m-d", $now);
            // 记录生成的memo数量（即提醒次数）
            $company['data']['notify_times'] = $notifyTimes + 1;
            // 提醒奇数次需要上传报告
            $uploadReport = false;
            if ($notifyTimes % 2) {
                $uploadReport = true;
            }
            $this->em->replace_bare($company);
            // 生成memo数据
            $memoUUID = $this->addMemoByCompany($company, $list, $uploadReport);
            // 发送提醒消息
            $this->sendMessage($memoUUID, $config['msg']['notify'], $data);
        }

        return $company;
    }

    /**
     * task/2022-00021 业绩对赌/回购 预警
     * @param $company array company data
     * @param $config array memo_config contract_status
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function monitorContractStatus($company, $config)
    {
        // 仅在四个季度最后一天判断是否发送提醒
        $now = time();
        $monthDay = date("m-d", $now);
        if (!in_array($monthDay, ['03-31', '06-30', '09-30', '12-31'])) {
            return $company;
        }
        // 上次通知日期
        $lastNotifyDate = $company['data']['last_notify_date2'];
        $deadline = strtotime($lastNotifyDate) + $this->getSecondsByTimeQuantum(0, 6);
        if ($deadline <= $now) {
            // 替换模板变量
            $data = array_merge($company, $company['data']);
            $data = array_merge($data, ['year' => date("Y", $now), 'month' => date("m", $now), 'day' => date("d", $now)]);
            // 提醒/抄送文本
            foreach ($config['memo'] as $field => $tpl) {
                $list[$field] = t($tpl, $data);
            }
            // 预警类型
            $list['monitor_type'] = 'contract_status';
            $list['monitor_type_label'] = '业绩对赌/回购';
            // 更新项目中的上次提醒日期
            $company['data']['last_notify_date2'] = date("Y-m-d", $now);
            $this->em->replace_bare($company);
            // 生成memo数据
            $memoUUID = $this->addMemoByCompany($company, $list);
            // 发送提醒消息
            $this->sendMessage($memoUUID, $config['msg']['notify'], $data);
        }

        return $company;
    }

    /**
     * task/2022-00021 投后管理报告 预警
     * @param $company array company data
     * @param $config array memo_config after_report
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function monitorAfterReport($company, $config)
    {
        // 仅在四个季度最后一天判断是否发送提醒
        $now = time();
        $monthDay = date("m-d", $now);
        if (!in_array($monthDay, ['03-31', '06-30', '09-30', '12-31'])) {
            return $company;
        }
        $lastNotifyDate = $company['data']['last_notify_date3'];        // 上一次提醒日期
        $reportStartDate = $company['data']['report_start_date'];       // 报告开始日期
        if (!$lastNotifyDate) {
            $lastNotifyDate = $reportStartDate;
        }
        $times = $company['data']['gy3ypinx33'];                        // 提交频次
        $times = mb_substr($times, 2, 1);
        $deadline = strtotime($lastNotifyDate) + $this->getSecondsByTimeQuantum(0, 0, 0, $times);
        if ($deadline < $now) {
            // 替换模板变量
            $data = array_merge($company, $company['data']);
            $data = array_merge($data, ['year' => date("Y", $now), 'month' => date("m", $now), 'day' => date("d", $now)]);
            // 提醒/抄送文本
            foreach ($config['memo'] as $field => $tpl) {
                $list[$field] = t($tpl, $data);
            }
            // 预警类型
            $list['monitor_type'] = 'after_report';
            $list['monitor_type_label'] = '投后报告';
            // 更新项目中的上次提醒日期
            $company['data']['last_notify_date3'] = date("Y-m-d", $now);
            $this->em->replace_bare($company);
            // 生成memo数据
            $memoUUID = $this->addMemoByCompany($company, $list);
            // 发送提醒消息
            $this->sendMessage($memoUUID, $config['msg']['notify'], $data);
        }

        return $company;
    }

    /**
     * task/2022-00021 投后管理计划 预警
     * @param $company array company data
     * @param $config array memo_config after_report
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function monitorAfterPlan($company, $config)
    {
        $lastNotifyDate = $company['data']['last_notify_date4'];        // 上一次提醒日期
        $afterPlanDate = $company['data']['after_plan_date'];       // 报告开始日期
        $afterPlanMonth = intval(date("m", strtotime($afterPlanDate)));
        $now = time();
        $month = intval(date("m", $now));
        // 1-9月交割的项目需要提交当前年报告
        if (!$lastNotifyDate && $afterPlanMonth < 10) {
            $data = array_merge($company, $company['data']);
            $data = array_merge($data, ['year' => date("Y", $now), 'month' => date("m", $now), 'day' => date("d", $now)]);
            // 提醒/抄送文本
            foreach ($config['memo'] as $field => $tpl) {
                $list[$field] = t($tpl, $data);
            }
            // 预警类型
            $list['monitor_type'] = 'after_plan';
            $list['monitor_type_label'] = '投后管理计划';
            // 更新项目中的上次提醒日期
            $company['data']['last_notify_date4'] = date("Y-m-d", $now);
            $this->em->replace_bare($company);
            // 生成memo数据
            $memoUUID = $this->addMemoByCompany($company, $list);
            // 首次通知替换文本
            $config['msg']['notify'] = $config['msg']['first_notify'];
            // 发送提醒消息
            $this->sendMessage($memoUUID, $config['msg']['notify'], $data);

            return $company;
        }

        $monthDay = date("m-d", $now);
        // 2月1日生成memo
        if ($monthDay == '02-01' && strtotime($lastNotifyDate) != $now) {
            // 替换模板变量
            $data = array_merge($company, $company['data']);
            $data = array_merge($data, ['year' => date("Y", $now), 'month' => date("m", $now), 'day' => date("d", $now)]);
            // 提醒/抄送文本
            foreach ($config['memo'] as $field => $tpl) {
                $list[$field] = t($tpl, $data);
            }
            // 预警类型
            $list['monitor_type'] = 'after_report';
            $list['monitor_type_label'] = '投后报告';
            // 更新项目中的上次提醒日期
            $company['data']['last_notify_date4'] = date("Y-m-d", $now);
            $this->em->replace_bare($company);
            // 生成memo数据
            $memoUUID = $this->addMemoByCompany($company, $list);
            // 发送提醒消息
            $this->sendMessage($memoUUID, $config['msg']['notify'], $data);
        }

        return $company;
    }

    /**
     * task/2022-00021 批量发送消息提醒
     * @param string $eid memo uuid
     * @param array $config 消息模板配置
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function sendMessage($eid, $config, $data = [])
    {
        // 发送通知
        $elem = [
            'eid' => $eid,
            'link' => "?/memo/view/about/" . $eid,
            'tmpl' => 'link',
            'send_time' => time(),
            'sid' => $this->sid,
        ];

        // 整理通知的用户
        $members = $this->getMembers($config['member'], $data);
        $elem['type'] = 'memo_notify';
        $elem['content']= t($config['content'], $data);
        foreach($members as $member) {
            $this->msg_m->add($member, $elem);
        }
    }

    /**
     * task/2022-00021 根据配置整理用户uuid
     * @param array $uuids eg: user.xxx role.xxx field.xxx
     * @param array $data 字段数据，用于 field.xxx
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.1
     */
    public function getMembers($uuids, $data = [])
    {
        $members = [];
        foreach ($uuids as $uuid_) {
            list($type, $uuid) = explode('.', $uuid_);
            switch ($type) {
                case 'field':
                    $user = $data[$uuid];
                    break;
                case 'user':
                    $user = $uuid;
                    break;
                case 'role':
                    $user = load('m/space_usr_m')->get_by_role($uuid, 'uid');
                case 'structure':   // 暂无使用场景
                default:
                    continue;
                    break;
            }
            if (!$user) {
                continue;
            }
            if (is_array($user)) {
                $members = array_merge($members, $user);
                continue;
            }
            array_push($members, $user);
        }
        $members = array_unique(array_filter($members));

        return $members;
    }

    /**
     * task/2022-00021 检查memo
     * @param string $eid memo uuid
     * @param string $state memo类型
     * @param string $state 预警类型
     */
    public function checkMemo($eid, $state, $monitorType)
    {
        // 已完成不进行检查
        if ($state == '完成') {
            return true;
        }
        $config = load('m/config_m')->key('memo_config');
        $memo = $this->em->get_one($eid);
        $memo['data'] = _decode($memo['data']);
        // 获取关联项目
        $companyUUID = $memo['data']['glian'];
        $company = $this->em->get_one($companyUUID);
        $company['data'] = _decode($company['data']);
        $finished = true;
        switch ($monitorType) {
            case 'state_change':    // 项目状态变更
                if ($memo['data']['gyd81v6099'] === '不跟进') {
                    $memos = $this->em->get("AND `del` = 0
                        AND `type` = 'memo'
                        AND `data`->>'$.glian' = '$companyUUID'
                        AND `data`->>'$.monitor_type' = 'state_change'
                        AND `data`->>'$.flcsqifwd8' = '待办'
                        AND `uuid` <> '$eid'
                    ");
                    // 关闭该项目下的所有memo
                    foreach ($memos as $memo_) {
                        $memo_['data'] = _decode($memo_['data']);
                        $memo_['data']['flcsqifwd8'] = $memo_['data']['flcsqifwd8_label'] = '完成';
                        $this->em->replace_bare($memo_);
                    }
                    $list = ['入库', '初筛', '立项', '内核会', '投委会'];
                    if (!in_array($company['data']['state'], $list)) {
                        $memo['data']['flcsqifwd8'] = $memo['data']['flcsqifwd8_label'] = '完成';
                        break;
                    }
                    // 提醒 用户提交相应的流程
                    $stateChangeConfig = $config['state_change']['msg']['notify'];
                    switch ($company['data']['state']) {
                        case '入库':
                            $stateChangeConfig['content'] = '请提交不予初筛申请单流程';
                            break;
                        case '初筛':
                            $stateChangeConfig['content'] = '请提交不予立项申请单';
                            break;
                        case '立项':
                            $stateChangeConfig['content'] = '请提交不予内核申请单';
                            break;
                        case '内核会':
                            $stateChangeConfig['content'] = '请提交内核会通过项目终止申请单';
                            break;
                        case '投委会':
                            $stateChangeConfig['content'] = '请提交投委会通过项目终止申请单';
                            break;
                        default:
                            break;
                    }
                    $this->sendMessage($eid, $stateChangeConfig, $company['data']);
                    break;
                }
                if (!$memo['data']['progress']) {
                    $finished = false;
                    break;
                }
                if ($memo['data']['need_upload_report'] == '是' && !array_filter($memo['data']['progress_report'])) {
                    $finished = false;
                    break;
                }
                $company['data']['notify_times'] = 0;
                $this->sendMessage($memo['uuid'], $config['state_change']['msg']['cc'], $memo['data']);
                break;
            case 'contract_status':   //业绩对赌/回购
                if (!$memo['data']['contract_status']) {
                    $finished = false;
                    break;
                }
                $companyUUID = $memo['data']['glian'];
                $company = $this->em->get_one($companyUUID);
                $company['data'] = _decode($company['data']);
                // 投后履约状态
                $company['data']['gu36iq4k3p'] = $memo['data']['contract_status'];
                $company['data']['gu36iq4k3p_label'] = $memo['data']['contract_status_label'];
                // 未触发状态
                $company['data']['gu7w34592e'] = $memo['data']['not_trigger'];
                $company['data']['gu7w34592e_label'] = $memo['data']['not_trigger_label'];
                // 提醒次数归0
                $company['data']['notify_times'] = 0;
                $this->sendMessage($memo['uuid'], $config['contract_status']['msg']['cc'], $memo['data']);
                break;
            case 'after_report':    // 投后报告
                if (!$memo['data']['after_report'][1]) {
                    $finished = false;
                    break;
                }
                $companyUUID = $memo['data']['glian'];
                $company = $this->em->get_one($companyUUID);
                $company['data'] = _decode($company['data']);
                // 投后报告
                $company['data']['after_report'] = $memo['data']['after_report'];
                $company['data']['after_report_label'] = $memo['data']['after_report_label'];
                break;
            case 'after_plan':  // 投后管理计划
                if (!$memo['data']['plan_report'][1]) {
                    $finished = false;
                    break;
                }
                $companyUUID = $memo['data']['glian'];
                $company = $this->em->get_one($companyUUID);
                $company['data'] = _decode($company['data']);
                // 投后报告
                $company['data']['plan_report'] = $memo['data']['plan_report'];
                $company['data']['plan_report_label'] = $memo['data']['plan_report_label'];
            default:
                return false;
        }
        // 未提交相应字段
        if (!$finished) {
            return false;
        }
        // memo变更为完成
        $memo['data']['flcsqifwd8'] = $memo['data']['flcsqifwd8_label'] = '完成';
        $this->em->replace_bare($company);
        $this->em->replace_bare($memo);
    }

  /**
  * task/2022-02001 汇总返投信息
  * @param string $fundUUID fund uuid
  * @author stone <yqgao@pepm.com.cn>
  * @version v6.2
  */
  public function summaryBackInvest($fundUUID){
    if (!$fundUUID) {
      return false;
    }
    $list = [];
    $backList = [
      'gtifmwa1dd' => 'gtifmt5mqs',   // 返投维度 => 返投金额
      'h190u6emxy' => 'h190ud1ylo',   // 返投维度2 => 返投金额2
      'h190udh965' => 'h190udp6nx',   // 返投维度3 => 返投金额3
      'h190udzsrb' => 'h190ueb437',   // 返投维度4 => 返投金额4
      'h190uem59s' => 'h190uf3l43',   // 返投维度5 => 返投金额5
    ];
    // 返投项目
    //task/2022-13077 返投信息中完成额度计算脚本更改 https://oa.vc800.com/?/task/view/about/h86r7a9cgm
    //张振 <zzhang@pepm.com.cn>
    //release/v6.2
    //start
    $companyList = $this->em->get("AND `del` = 0 AND `type` = 'xmftxx' AND `data`->>'$.fund' = '$fundUUID' AND `data`->>'$.state' in ('投后','退出','全部退出')");
    //task/2022-13077 end
    foreach ($companyList as $company) {
      $data = _decode($company['data']);
      foreach ($backList as $area => $amount) {
        // 记录返投地区的返投金额
        if (!isset($list[$data[$area]])) {
          $list[$data[$area]] = 0;
        }
        $list[$data[$area]] += $data[$amount];
      }
    }
    // 返投信息
    $infos = $this->em->get("AND `del` = 0 AND `type` = 'ztftqkb' AND `data`->>'$.fund' = '$fundUUID'");
    // 更新返投信息
    foreach ($infos as $info) {
      $data = _decode($info['data']);
      $uuid = $info['uuid'];
      if (!isset($list[$uuid])) {
        continue;
      }
      // 更新完成额度
      $data['gu3lyukfkz'] = $list[$uuid];
      // 剩余任务
      $data['gu3lyuxasu'] = $data['gu3lyu8a6q'] - $data['gu3lyukfkz'];
      $info['data'] = $data;
      $this->em->replace_bare($info);
    }
  }

/**
     * task/2022-03594 汇总返投信息到基金 已完成返投额度
     * @param string $fundUUID fund uuid
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.2
     */
    //task/2022-14322 流程自动生成审批单、投后走投前不变更状态、基金已完成返投额度字段脚本失效了 
    public function summaryBackInvestAmount($fundUUID){
      if (!$fundUUID) {
        return false;
      }
      $fund = $this->em->get_one($fundUUID);
      $fund['data'] = _decode($fund['data']);
      if ($fund['data']['special'] == '是') {
        $isSpecial = true;
      }
      // 已完成金额
      $max_amount = array();
      // 返投项目
      //task/2022-14656 返投情况状态根据概况状态进行变更，已完成返投额度只计算投后、退出、完成退出状态  start
      $companyList = $this->em->get("AND `del` = 0 AND `type` = 'xmftxx' AND `data`->>'$.fund' = '$fundUUID' AND `data`->>'$.state' in ('投后','退出','全部退出')");
      //task/2022-14656 返投情况状态根据概况状态进行变更，已完成返投额度只计算投后、退出、完成退出状态  end
      $i = 0;
      $amount = 0;
      $num_amount = 0;
      foreach ($companyList as $company) {
        $data = _decode($company['data']);
        $max_amount[$i] = [$data['gtifmt5mqs'], $data['h190ud1ylo'], $data['h190udp6nx'], $data['h190ueb437']];
        $i++;
        $num_amount += $data['gtifmt5mqs'] + $data['h190ud1ylo'] + $data['h190udp6nx'] + $data['h190ueb437']; // 累加所有金额
      }
      foreach ($max_amount as $value) {
        if ($isSpecial) {  // 当  是否属于特殊基金 字段为   "是" 的时候走该分支
          $amount += max($value);  // 将每一行最大值加在一起赋值给 h19oz1wmpo 字段中
        }
      }
      if ($isSpecial) {
        $fund['data']['h19oz1wmpo'] = $amount;
      }else {
        $fund['data']['h19oz1wmpo'] = $num_amount;
      }
      $this->em->replace_bare($fund);
    }

    /**
     * task/2022-01999 汇总退出项目到基金
     * @param string $fundUUIDs fund uuids
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.2
     */
    public function countExitCompany($fundUUIDs)
    {
        if (!$fundUUIDs) {
            return false;
        }
        $baseSQL = " AND `del` = 0 AND `type` = 'company' AND `data`->>'$.is_exit' = '1'";
        $fundUUIDs = explode(",", $fundUUIDs);
        foreach ($fundUUIDs as $fundUUID) {
            $fund = $this->em->get_one($fundUUID);
            if (!$fund) {
                continue;
            }
            $fund['data'] = _decode($fund['data']);
            // 获取所有基金投资项目
            $sql = $baseSQL . " AND `data`->>'$.fund' LIKE '%$fundUUID%'";
            // 已退出项目数量
            $fund['data']['gtcmkogyrc'] = count($this->em->get($sql));

            $this->em->replace_bare($fund);
        }
    }
     /**
     * task/2022-06286 汇总已投项目到基金（投后，退出，全部退出三种状态都要统计）
     * @param string $eid   项目的uuid
     * @author zhanzghen <zzhang@pepm.com.cn>
     * @version v6.2
     */
    public function count_ytxm_fund($eid)
    {
        if (!$eid) {
            return false;
        }
        // 获取sid,之后的查询都需要带上这个查询 
        $sid = WEBSID;
        $company_info = $this->em->get_one($eid);
        $company_info['data'] = _decode($company_info['data']);
        $fund_uuid = $company_info['data']['fund'];//根据项目uuid获取项目对应的基金uuid
        if($fund_uuid){
                $fund_info = $this->em->get_one($fund_uuid); //基金的信息
                $fund_info['data'] = _decode($fund_info['data']);
                //查找基金对应的所有状态为投后，退出，全部退出的项目
                $sql = "select * from entity where  `del` = 0 and sid='$sid' AND `type` = 'company' AND `data`->>'$.state' in ('投后','退出','全部退出') and `data`->>'$.fund'='".$fund_uuid."'";
                $count_num = count($this->em->db->query($sql));
                $fund_info['data']['h3q38gqvac'] = $count_num;
                $this->em->replace_bare($fund_info);
            
        } 
    }

    /**
     * task/2022-01998 新增日报生成归属周
     * @param string $userID user uuid
     * @param string $inWeek week times eg: 2022-W12
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.2
     */
    public function addDaily($userUUID, $inWeek)
    {
        if (!$userUUID || !$inWeek) {
            return false;
        }
        // 根据周次计算时间区间
        $getDateStr = function($weekTime) {
            $year = intval(substr($weekTime,0,4));                          // 年份
            $weeks = intval(substr($weekTime, strlen($weekTime) - 2));      // 周次
            // 根据1月1日计算当前年第一周的第一天
            $firstDay = 1;
            $week = intval(date('N', strtotime($year . '-01-' . $firstDay)));
            if ($week !== 1) {
                $firstDay += 7 - $week + 1;
            }
            $time = strtotime($year . '-01-' . $firstDay);
            $day = 86400;       // 一天的秒数
            // 计算出目标周次的区间
            $time += $day * ($weeks - 1) * 7;       // 第一天
            $timeEnd = $time + $day * 6;            // 最后一天

            return date("Y-m-d", $time) . ' 至 ' . date("Y-m-d", $timeEnd);
        };

        $member = $this->em->get("AND `del` = 0
            AND `type` = 'usr_rb'
            AND `data`->>'$.name' = '$userUUID'
        ")[0];
        $user = $this->space_usr_m->profile($this->sid, $userUUID, true);
        $user = $user['data']['name'];
        if (!$member) {
            $member = [
                'type' => 'usr_rb',
                'data' => [
                    'name' => $userUUID,
                    'name_label' => $user,
                ],
            ];

            $this->em->add($member);
        }

        $week = $this->em->get("AND `del` = 0
            AND `type` = 'usr_gzrb'
            AND `data`->>'$.name' = '$userUUID'
            AND `data`->>'$.gsz' = '$inWeek'
        ");
        if (!$week) {
            $week = [
                'type' => 'usr_gzrb',
                'data' => [
                    'name' => $userUUID,          // 日报填写人
                    'name_label' => $user,
                    'gsz' => $inWeek,           // 归属周
                    'h0ajaj1817' => $getDateStr($inWeek),         // 期间备注
                ],
            ];

            $this->em->add($week);
        }
    }

    /**
     * task/2022-02957 复制项目数据
     * @param array $point
     * @param array $flow
     * @author stone <yqgao@pepm.com.cn>
     * @version v6.2
     */
    public function hookCopyCompany($point, $flow)
    {
        $uuid = $flow['form_data']['name'];
        $companyOld = $this->em->get_one($uuid);
        $companyOld['data'] = _decode($companyOld['data']);
        $newLabel = $flow['form_data']['name_label'];
        $companyUUID = $flow['form_data']['h0vuk8tj6b'];
        $company = $this->em->get_one($companyUUID);
        if (!$company) {
            return false;
        }
        // 复制项目数据
        $company['data'] = _decode($company['data']);
        $company['uuid'] = $uuid;
        $company['data']['name'] = $companyOld['data']['name'];     // 项目简称
        $company['data']['fullname'] = $companyOld['data']['fullname'];         // 项目全称
        $company['data']['glpnwgnzp0'] = $companyOld['data']['glpnwgnzp0'];     // 项目编号
        unset($company['id']);

        $this->em->replace_bare($company);

        // 复制流程及相关数据
        $types = [
            'tzywps',   // 初筛上会申请单
            'flow_bycs',    // 不予初筛申请单
            'folw_lx',      // 立项上会申请单
            'flow_bylx',    // 不予立项申请单
            'flow_bynh',    // 不予内核申请单
            'flow_shsq',    // 内核会上会申请单
            'nhhbjp_flow',  // 内核会表决票
            'flow_ytjtg',   // 内核会有条件同意表决票审批单
            'flow_nhhshbg', // 内核会上会方案变更审批单
            'nhhjysp_flow', // 内核会决议审批单
            'flow_twhtgxmzz',   // 投委会通过项目终止申请单
        ];
        $types = "'" . implode("','", $types) . "'";
        $flows = $this->em->get("AND `del` = 0
            AND `type` IN ($types)
            AND `data`->>'$.eid' = '$companyUUID'
        ");
        foreach($flows as $flow) {
            // 流程
            $flow['data'] = _decode($flow['data']);
            $flowUUID = $flow['uuid'];
            $newFlow = uuid();
            $flow['uuid'] = $newFlow;
            $flow['data']['eid'] = $uuid;
            foreach ($flow['data'] as $key => $value) {
                if (!is_array($value)) {
                    continue;
                }
                $flow['data'][$key]['eid'] = $newFlow;
                if (isset($flow['data'][$key]['name'])) {
                    $flow['data'][$key]['data']['name'] = $uuid;
                    $flow['data'][$key]['data']['name_label'] = $newLabel;
                }
            }
            // dump($flow);
            $this->em->add($flow);
            // _flow_index
            $flowIndex = $this->em->get("AND `del` = 0
                AND `type` = '_flow_index'
                AND `data`->>'$._rel' = '$flowUUID'
            ")[0];
            if ($flowIndex) {
                $flowIndex['data'] = _decode($flowIndex['data']);
                $flowIndex['uuid'] = uuid();
                $flowIndex['data']['_rel'] = $newFlow;
                $this->em->add($flowIndex);
            }
        }

        // file
        $files = load("m/file_m")->get("AND `del` = 0
            AND `doc_type` = 'company'
            AND `eid` = '$companyUUID'
        ");
        foreach ($files as $file) {
            $file['uuid'] = uuid();
            $file['eid'] = $uuid;
            load("m/file_m")->add($file);
        }
    }

    /**
     * 从基金模块财务账面估值新增数据填充至投后概况
     * 
     * task/2022-04424 https://oa.vc800.com/?/task/view/about/h25ld8c09z
     * @param $companyUUID company uuid
     * @author 高毅琼 <yqgao@pepm.com.cn>
     * @version v6.2
     *
    *    * 从基金模块财务账面估值新增数据填充至投后概况新增但带入字段
    * @task    2022-05231 https://oa.vc800.com/?/task/view/about/h2tyg2u3r5
    * @param   string    $value [description]
    * @author  张汪明 <@pepm.com.cn>
    */
    public function updateValuation($companyUUID)
    {
        $company = $this->em->get_one($companyUUID);
        if (!$company) {
            return false;
        }
        $company['data'] = _decode($company['data']);
        $finance = $this->em->get("AND `del` = 0
            AND `type` = 'cashflow_cwbggz'
            AND `data`->>'$.company' = '$companyUUID'
            ORDER BY `data`->>'$.h1o0ncqdho' DESC
        ")[0];
        if ($finance) {
            $data = _decode($finance['data']);
            // jhqin 2024-14210
            $company['data']['h1o0nyoav0'] = $company['data']['h1o0nyoav0_label'] = $data['h1o0nyoav0'];       // 估值方法
            $company['data']['h1o0nzokxb'] = $data['h1o0nzokxb'];       // 财务账面估值
            $company['data']['h1o0ncqdho'] = $data['h1o0ncqdho'];       //估值基准日
        }

        $this->em->replace($company, 'company', 'update', false, false, false);
    }

   /**
   * 流程中心显示项目名称/流程名称
   *
   * @task    2022-04314 https://oa.vc800.com/?/task/view/about/h23arfzgib
   * @param   string    $value [description]
   * @author  张汪明 <@pepm.com.cn>
   * @version release/v6.2
   */
    public function batchUpdateFlowIndex()
    {
        $cm = load("m/config_m");
        $flowIndexList = $this->em->get("AND `del` = 0 AND `type` = '_flow_index'");
        $config = $cm->key('copy_flow_data');
        if(!$config) {
            return false;
        }
        foreach ($flowIndexList as $flowIndex) {
            $data = _decode($flowIndex['data']);
            $flowUUID = $data['_rel'];
            $flow = load("m/entity_m")->get_one($flowUUID);
            $flowConfig = $config[$flow['type']];
            if (!$flowConfig) {
                continue;
            }
            $flow['data'] = _decode($flow['data']);
            $formData = load("m/flow_m")->get_clean_data_new($flow);
            // 复制数据
            foreach ($flowConfig as $field => $flowField) {
                $flowField1=$flowField.'_label';
                if(!empty($formData[$flowField1])){
                    $data[$field] = $formData[$flowField1];
                }else{
                    $data[$field] = $formData[$flowField];
                }
            }
            $flowIndex['data'] = $data;
            $this->em->replace_bare($flowIndex);
        }
    }
   /**
     * task/2022-05161 汇总项目到团队分析
     * @param string company eid
     * @author stone <zzhang@pepm.com.cn>
     * @version v6.2
     */
    public function update_tdfx($eid)
    {
        //$eid   当前项目的uuid
        // 引入entity_m
        $em = load("m/entity_m");
        $history = load("m/history_m");
        // 获取sid,之后的查询都需要带上这个查询 
        $sid = WEBSID;
        $rel_id = 'h1sshaitqm';
        //先通过eid查询entity表中的data数据，在跟据data数据中的gwa39zwwsx是否有值，有值的话就是责任MD，相反如果是team_fzrA就是责任经理
        // 定义一个名为choose_info的变量接收查询的数据
        $choose_info = $em->get_one($eid);  // 这个项目的数据
        $choose_info = _decode($choose_info['data']);
        //获取这个项目修改之前的最近一条数据
        $his_sql = "select * from history where eid='$eid' and extra='management' order by id desc limit 1,1";
        $history_info = $history->db->query($his_sql);
        $history_info = _decode($history_info[0]['data']);


        //取出接收查询的数据`data`字段
        if ($choose_info['gwa39zwwsx']) {  //代表是项目责任MD
            $new_gwa39zwwsx = explode(',', $choose_info['gwa39zwwsx']);
            if ($history_info) {
                $new_his_gwa39zwwsx = explode(',', $history_info['gwa39zwwsx']);
                $diffent = array_diff_assoc($new_his_gwa39zwwsx, $new_gwa39zwwsx); //当前项目和修改前的项目数据中的责任md做对比，找出删除的责任md
                if ($diffent) { //如果有删除的责任md
                    foreach ($diffent as $dif_key => $dif_val) {
                        $dif_sql = "update entity set del=1 where type='jscmd' and sid='$sid' and `data`->>'$._rel'='$rel_id'  and `data`->>'$.name'='$dif_val'";
                        $em->db->query($dif_sql);  //删除责任md对应的计算表数据
                    }
                    $new_gwa39zwwsx = array_unique(array_merge($diffent, $new_gwa39zwwsx));  //再把删除的责任md加入循环中重新计算
                }
            }
            //责任md有时候可能是几个人同时负责一个项目,循环出每个md
            if ($new_gwa39zwwsx) {

                foreach ($new_gwa39zwwsx as $p => $q) {
                    $sql = "AND `type`='jscmd' AND `del` =0 AND `sid` = '$sid'  AND `data`->>'$._rel'='$rel_id' AND data->>'$.name'='$q'";

                    $info = $em->get($sql);  //每个责任MD对应的计算表数据
                    if ($info) {  //如果有值直接使用
                        //取出接收查询的数据`data`字段
                        $new_info = $info[0];
                        //取出接收查询的数据`data`字段
                        $new_info['data'] = _decode($new_info['data']);
                    } else {  //如果没值新建一条
                        if ($q) {
                            $space_usr_info = load("m/space_usr_m")->profile($sid, $q); //责任md的信息，使用名称
                            $data = array(
                                "h1sy90yb0d" => 0,
                                "name_optgroup" => "",
                                "name_label" => $space_usr_info['name'],
                                "name" => $q,
                                "h1sy90yb0d_label" => "个",
                                "h1sy91ayp6" => 0,
                                "h1sy91ayp6_label" => "万",
                                "h1sy91n78s" => 0,
                                "h1sy91n78s_label" => "万",
                                "h1sy925uqo" => 0,
                                "h1sy925uqo_label" => "万",
                                "h26sv3s0tx" => 0,
                                "h26subgop1" => 0,
                                "h26subsl3l" => 0,
                                "_rel" => $rel_id,
                                "_rel_view" => "jsc",
                                "_jscmd_required_flag" => "0",
                                "seg" => "jscmd",
                                "notify_to" => array(),
                                "cache" => "0",
                                "update_date" => time(),
                                "privilege" => array(
                                    "list" => array(),
                                ),
                            );
                            $add_info = array(
                                'uuid' => uuid(),
                                'data' => $data,
                                'sid' => WEBSID,
                                'name' => $q,
                                'input_people' => $q,
                                'input_date' => time(),
                                'update_date' => time(),
                                'type' => 'jscmd',
                                'del' => 0
                            );
                            $em->replace($add_info);
                        }
                        $sql = "AND `type`='jscmd' AND `del` =0 AND `sid` = '$sid'  AND `data`->>'$._rel'='$rel_id' AND data->>'$.name'='$q'";

                        $info = $em->get($sql);  //每个责任MD对应的计算表数据
                        $new_info = $info[0];

                        //取出查询的数据`data`字段
                        $new_info['data'] = _decode($new_info['data']);
                    }

                    $sql1 = "AND `type`= 'company' AND `del` =0  AND `data`->>'$.state' in ('投后','退出','全部退出') AND `sid` = '$sid' AND  data->>'$.gwa39zwwsx' like '%$q%'";

                    $pro = $em->get($sql1);   //查询出项目责任MD对应的项目 
                    $new_info['data']['h1sy90yb0d'] = count($pro); //项目总数量
                    $total_h26sv3s0tx = 0;
                    $total_h26subgop1 = 0;
                    $total_h26subsl3l = 0;
                    $total_h1sy91ayp6 = 0;
                    $total_h1sy91n78s = 0;
                    $total_h1sy925uqo = 0;
                    if ($pro) {
                        //初始化数据
                        foreach ($pro as $pro_key => $pro_val) {
                            // 取出项目的所有字段
                            $pro_data = _decode($pro_val['data']);
                            // 定义变量来存最终的值
                            $total_h1sy91ayp6 += $pro_data['fvf2axxeoo']; //累计投资金额
                            $total_h1sy91n78s += $pro_data['gwftv2lek6']; //退出合计金额
                            $total_h1sy925uqo += ($pro_data['gwftv2lek6'] + $pro_data['ljxmcysy'] + $pro_data['h117atca6u']); //项目总收益
                            if ($pro_data['state'] == '投后') {
                                $total_h26sv3s0tx += 1;
                            }
                            if ($pro_data['state'] == '退出') {
                                $total_h26subgop1 += 1;
                            }
                            if ($pro_data['state'] == '全部退出') {
                                $total_h26subsl3l += 1;
                            }
                        }

                        $new_info['data']['h1sy91ayp6'] = $total_h1sy91ayp6; //累计投资金额
                        $new_info['data']['h1sy91n78s'] = $total_h1sy91n78s; //退出合计金额
                        $new_info['data']['h1sy925uqo'] = $total_h1sy925uqo; //项目总收益
                        $new_info['data']['h26sv3s0tx'] = $total_h26sv3s0tx; //项目投后数量
                        $new_info['data']['h26subgop1'] = $total_h26subgop1; //项目退出数量
                        $new_info['data']['h26subsl3l'] = $total_h26subsl3l; //项目全部退出数量   
                        // 将改变的值写进数据库
                        $em->replace($new_info);
                    }
                }
            }
        }
        // 查询对应责任经理下的项目
        if ($choose_info['team_fzrA']) {   //代表是项目责任经理
            $new_team_fzrA = explode(',', $choose_info['team_fzrA']);
            if ($history_info) {
                $new_his_team_fzrA = explode(',', $history_info['team_fzrA']);
                $jl_diffent = array_diff_assoc($new_his_team_fzrA, $new_team_fzrA);
                if ($jl_diffent) {
                    foreach ($jl_diffent as $jl_dif_key => $jl_dif_val) {
                        $jl_dif_sql = "update entity set del=1 where type='jscjl' and sid='$sid' and `data`->>'$._rel'='$rel_id'  and `data`->>'$.name'='$jl_dif_val'";
                        $em->db->query($jl_dif_sql);
                    }
                    $new_team_fzrA = array_unique(array_merge($jl_diffent, $new_team_fzrA));
                }
            }
            //有时候经理可能是几个人同时负责一个项目,循环出每个经理
            if ($new_team_fzrA) {
                foreach ($new_team_fzrA as $key => $val) {
                    $sql = "AND `type`='jscjl' AND `del` =0 AND `sid` = '$sid'  AND `data`->>'$._rel'='$rel_id' AND data->>'$.name'='$val'";
                    $jl_info = $em->get($sql);  //每个责任经理对应的计算表数据
                    if ($jl_info) {  //如果有值直接使用
                        //取出接收查询的数据`data`字段
                        $jl_new_info = $jl_info[0];
                        //取出接收查询的数据`data`字段
                        $jl_new_info['data'] = _decode($jl_new_info['data']);
                    } else {  //如果没值新建一条
                        if ($val) {
                            $jl_space_usr_info = load("m/space_usr_m")->profile($sid, $val); //责任经理的信息，使用名称
                            $jl_data = array(
                                "h1oo7xgqhi" => 0,
                                "name_optgroup" => "",
                                "name_label" => $jl_space_usr_info['name'],
                                "name" => $val,
                                "h1oo7xgqhi_label" => "个",
                                "h1oo90or54" => 0,
                                "h1oo90or54_label" => "万",
                                "h1oo919xgl" => 0,
                                "h1oo919xgl_label" => "万",
                                "h1sybpyb58" => 0,
                                "h1sybpyb58_label" => "万",
                                "h27s6pytpy" => 0,
                                "h27s6qgvl0" => 0,
                                "h27s86v7f2" => 0,
                                "_rel" => $rel_id,
                                "_rel_view" => "jsc",
                                "_jscjl_required_flag" => "0",
                                "seg" => "jscjl",
                                "notify_to" => array(),
                                "cache" => "0",
                                "update_date" => time(),
                                "privilege" => array(
                                    "list" => array(),
                                ),
                            );
                            $jl_add_info = array(
                                'uuid' => uuid(),
                                'data' => $jl_data,
                                'sid' => WEBSID,
                                'name' => $val,
                                'input_people' => $val,
                                'input_date' => time(),
                                'update_date' => time(),
                                'type' => 'jscjl',
                                'del' => 0
                            );
                            $em->replace($jl_add_info);
                        }
                        $sql = "AND `type`='jscjl' AND `del` =0 AND `sid` = '$sid'  AND `data`->>'$._rel'='$rel_id' AND data->>'$.name'='$val'";
                        $jl_info = $em->get($sql);  //每个责任经理对应的计算表数据
                        $jl_new_info = $jl_info[0];
                        //取出查询的数据`data`字段
                        $jl_new_info['data'] = _decode($jl_new_info['data']);
                    }
                    $sql2 = "AND `type`= 'company' AND `del` =0  AND `data`->>'$.state' in ('投后','退出','全部退出') AND `sid` = '$sid' AND  data->>'$.team_fzrA' like '%$val%'";
                    $jl_pro = $em->get($sql2);   //查询出项目责任经理对应的项目 
                    $jl_new_info['data']['h1oo7xgqhi'] = count($jl_pro); //项目总数量
                    $total_h1oo90or54 = 0;
                    $total_h1oo919xgl = 0;
                    $total_h1sybpyb58 = 0;
                    $total_h27s6pytpy = 0;
                    $total_h27s6qgvl0 = 0;
                    $total_h27s86v7f2 = 0;
                    if ($jl_pro) {
                        //初始化数据
                        foreach ($jl_pro as $jl_key => $jl_pro_val) {
                            // 取出项目的所有字段
                            $jl_pro_data = _decode($jl_pro_val['data']);
                            // 定义变量来存最终的值
                            $total_h1oo90or54 += $jl_pro_data['fvf2axxeoo']; //累计投资金额
                            $total_h1oo919xgl += $jl_pro_data['gwftv2lek6']; //退出合计金额
                            $total_h1sybpyb58 += ($jl_pro_data['gwftv2lek6'] + $jl_pro_data['ljxmcysy'] + $jl_pro_data['h117atca6u']); //项目总收益
                            if ($jl_pro_data['state'] == '投后') {
                                $total_h27s6pytpy += 1;
                            }
                            if ($jl_pro_data['state'] == '退出') {
                                $total_h27s6qgvl0 += 1;
                            }
                            if ($jl_pro_data['state'] == '全部退出') {
                                $total_h27s86v7f2 += 1;
                            }
                        }
                        $jl_new_info['data']['h1oo90or54'] = $total_h1oo90or54; //累计投资金额
                        $jl_new_info['data']['h1oo919xgl'] = $total_h1oo919xgl; //退出合计金额
                        $jl_new_info['data']['h1sybpyb58'] = $total_h1sybpyb58; //项目总收益
                        $jl_new_info['data']['h27s6pytpy'] = $total_h27s6pytpy; //项目投后数量
                        $jl_new_info['data']['h27s6qgvl0'] = $total_h27s6qgvl0; //项目退出数量
                        $jl_new_info['data']['h27s86v7f2'] = $total_h27s86v7f2; //项目全部退出数量  
                        // 将改变的值写进数据库
                        $em->replace($jl_new_info);
                    }
                }
            }
        }
    }

    /**
     * 驾驶舱-项目分析-IRR计算
     *
     * @task    2022-04794 https://oa.vc800.com/?/task/view/about/h2h2ye5p53
     * @param   string    $value [description]
     * @author  龙大胜 <@pepm.com.cn>
     * @version release/v6.2
   */
    function cashflow_IRR($company_uuid){
        if(!$company_uuid) return;
        $money=[];
        $time=[];
        //通过项目目id，查找到这个项目的现金流
        $cashflow_data=$this->em->get(" and del=0 and type='cashflow_portfolio' and data->>'$.company'='$company_uuid' ");
        foreach ($cashflow_data as $k => $v) {
        $v["data"]=_decode($v["data"]);
        $cf_type=$v["data"]["cf_type"];
        switch ($cf_type) {
                case '(流入)退出款':
                    $money[]=floatval($v["data"]["fukuanjine"]);
                    $time[]=strtotime($v["data"]["fukuanshijian"]);
                    break;
                case '(流出)投资款':
                    $money[]=-floatval($v["data"]["fukuanjine"]);
                    $time[]=strtotime($v["data"]["fukuanshijian"]);
                    break;
                case '(流入)持有期间收益':
                    $money[]=floatval($v["data"]["fukuanjine"]);
                    $time[]=strtotime($v["data"]["fukuanshijian"]);
                    break;
                case '(流入)其他收益':
                    $money[]=floatval($v["data"]["fukuanjine"]);
                    $time[]=strtotime($v["data"]["fukuanshijian"]);
                    break;
                case '(流出)交易费用':
                    $money[]=-floatval($v["data"]["fukuanjine"]);
                    $time[]=strtotime($v["data"]["fukuanshijian"]);
                    break;
                case '(流入)交易费用':
                    $money[]=floatval($v["data"]["fukuanjine"]);
                    $time[]=strtotime($v["data"]["fukuanshijian"]);
                    break;
        }
        }
        //通过项目目id，查找到这个项目的最新的估值信息
        $cashflow_cwbggz_data=$this->em->get(" and del=0 and type='cashflow_cwbggz' and data->>'$.company'='$company_uuid' order by data->>'$.h1o0ncqdho' desc ")[0];
        $cashflow_cwbggz_data["data"]=_decode($cashflow_cwbggz_data["data"]);
        //估值金额
        $money[]=floatval($cashflow_cwbggz_data["data"]["h1o0nzokxb"]);
        //估值基准日
        $time[]=strtotime($cashflow_cwbggz_data["data"]["h1o0ncqdho"]);

        //通过项目目id，查找到这个项目的增值税
        $cashflow_portfolio_zzs=$this->em->get(" and del=0 and type='cashflow_portfolio_zzs' and data->>'$.company'='$company_uuid'");
        foreach ($cashflow_portfolio_zzs as $key => $value) {
            $value["data"]=_decode($value["data"]);
            $money[]=-floatval($value["data"]["fukuanjine1"]);
            $time[]=strtotime($value["data"]["fukuanshijian1"]);
        }
        //查出项目数据
        $company_data=$this->em->get_one($company_uuid);
        $company_data["data"]=_decode($company_data["data"]);
        //计算IRR
        $irr = load('lib/financial')->XIRR($money, $time);
        $number = number_format($irr*100,2);
        //task/2022-11298 https://oa.vc800.com/?/task/view/about/h78dx11n7r 已投项目IRR可自动计算也可手动修改 start
        if($number != "0.00"){
          $company_data["data"]["IRR"] = $number;
        }
        //task/2022-11298 end
        $this->em->replace_bare($company_data);
    }
    
    /**
     * 根据客户上传的财务报表后自动填充数值
     *
     * @task    2022-04780 https://oa.vc800.com/?/task/view/about/h2g6gpiknh
     * @param   string    $value [description]
     * @author  龙大胜 <@pepm.com.cn>
     * @version release/v6.2
   */
  function profit_report_excel()
  {
      $entity_m = load('m/entity_m');
      $file_m = load('m/file_m');

      $excel_form_data = $_POST['excel_form_data'];
      $eid = $_POST['eid'];
      $file_uuid = $_POST['file_uuid'];
      
      $file = $file_m->get($file_uuid);
      if (!$file) {
          ajax_return(['ok' => 0, 'msg' => '解析错误，未找到附件']);
      }
      $file_path = $file['file'];

      load('lib/PHPExcel', false);

      $objReader = PHPExcel_IOFactory::createReader('Excel2007');
      $objPHPExcel = $objReader->load($file_path);

      //解析Portfolio Company Template这个sheet
      $excel = $this->get_excel_data($objPHPExcel,0);

      $form_data = [];
      $form_data = [
          'fsz6tqz4gz' => $excel[5][1], //营业收入
          'gu14qkwn0j' => $excel[6][1], //营业成本
          'h2g5zywsqq' => $excel[7][1], //税金及附加
          'gu15mstj4x' => $excel[8][1], //销售费用（万）
          'gu15mtyvk0' => $excel[9][1], //管理费用（万）
          'gu15mut3bf' => $excel[10][1], //财务费用（万）
          'h2g60o90ef' => $excel[11][1], //资产减值损失
          'h2g61h4mt3' => $excel[12][1], //公允价值变动收益
          'h2g61q3lr0' => $excel[13][1], //投资收益
          'h2g62cqpcw' => $excel[14][1], //对联营企业和合营企业的投资收益
          'h2g68vira4' => $excel[15][1], //营业利润
          'h2g65y7gel' => $excel[16][1], //加：营业外收入
          "h2g65ysjuk" =>$excel[17][1],//减：营业外支出

          "h2g66rjbtk" =>$excel[18][1],//非流动资产处置损失
          "h2g68uwtvz" =>$excel[19][1],//利润总额
          "h2g6aaxy3u" =>$excel[20][1],//减：所得税费用
          "h2g6b72fux" =>$excel[21][1],//净利润
          "h2g6cko33x" =>$excel[23][1],//基本每股收益
          "h2g6clb6tq" =>$excel[24][1],//稀释每股收益

      ];
      $alldata = [
          'ok' => 1,
          'form_data' => $form_data
      ];
      ajax_return($alldata);
  }

  //资产负债表
  function liabilities_report_excel()
  {
      $entity_m = load('m/entity_m');
      $file_m = load('m/file_m');

      $excel_form_data = $_POST['excel_form_data'];
      $eid = $_POST['eid'];
      $file_uuid = $_POST['file_uuid'];
      
      $file = $file_m->get($file_uuid);
      if (!$file) {
          ajax_return(['ok' => 0, 'msg' => '解析错误，未找到附件']);
      }
      $file_path = $file['file'];

      load('lib/PHPExcel', false);

      $objReader = PHPExcel_IOFactory::createReader('Excel2007');
      $objPHPExcel = $objReader->load($file_path);

      //解析Portfolio Company Template这个sheet
      $excel = $this->get_excel_data($objPHPExcel,0);

      $form_data = [];
      $form_data = [
          'huobizijin' => $excel[6][1], //货币资金
          'jyxjrzc' => $excel[7][1], //交易性金融资产
          'yingshoupiaoju' => $excel[8][1], //应收票据
          'yingshouzhangkuan' => $excel[9][1], //应收账款
          'yufukuanxiang' => $excel[10][1], //预付款项
          'yingshoulixi' => $excel[11][1], //应收利息
          'h2g3ek6ici' => $excel[12][1], //应收股利
          'qtyskhj' => $excel[13][1], //其他应收款
          'cunhuo' => $excel[14][1], //存货
          'ynndqdfldzc' => $excel[15][1], //一年内到期的非流动资产
          'qtldzc' => $excel[16][1], //其他流动资产
          'ldzchj' => $excel[17][1], //流动资产合计

          "kgcsjrzc" =>$excel[19][1],//可供出售金融资产
          "cyzdqtz" =>$excel[20][1],//持有至到期投资
          "cqysk" =>$excel[21][1],//长期应收款
          "cqgqtz" =>$excel[22][1],//长期股权投资
          "tzxfdc" =>$excel[23][1],//投资性房地产
          "gudingzichan" =>$excel[24][1],//固定资产
          "zaijiangongcheng" =>$excel[25][1],//在建工程
          'h2g3jj9i1a' => $excel[26][1], //工程物资
          'h2g3kiijrx' => $excel[27][1], //固定资产清理
          'h2g5hnsz94' => $excel[28][1], //生产性生物资产
          'h2g5id4krp' => $excel[29][1], //油气资产
          'wuxingzichan' => $excel[30][1], //无形资产
          'kaifazhichu' => $excel[31][1], //开发支出
          'shangyu' => $excel[32][1], //商誉
          'cqdtfy' => $excel[33][1], //长期待摊费用
          'dysdszc' => $excel[34][1], //递延所得税资产
          'qtfldzc' => $excel[35][1], //其他非流动资产
          'fldzchj' => $excel[36][1], //非流动资产合计
          'zichanzongji' => $excel[37][1], //资产总计

          "duanqijiekuan" =>$excel[6][4],//短期借款
          'jyxjrfz' => $excel[7][4], //交易性金融负债
          'yingfupiaoju' => $excel[8][4], //应付票据
          'yingfuzhangkuan' => $excel[9][4], //应付账款
          'yushoukuanxiang' => $excel[10][4], //预收款项
          'yfzgxc' => $excel[11][4], //应付职工薪酬
          'yingjiaoshuifei' => $excel[12][4], //应交税费
          'yingfulixi' => $excel[13][4], //应付利息
          'yingfuguli' => $excel[14][4], //应付股利
          'qtyfk' => $excel[15][4], //其他应付款
          'ynndqdfldfz' => $excel[16][4], //一年内到期的非流动负债
          'h2g5mkkpnz' => $excel[17][4], //其他流动负债

          'changqijiekuan' => $excel[20][4], //长期借款
          "h2g5opnetw" =>$excel[21][4],//应付债券
          'cqyfk' => $excel[22][4], //长期应付款
          'zxyfk' => $excel[23][4], //专项应付款
          'yujifuzhai' => $excel[24][4], //预计负债
          'dysdsfz' => $excel[25][4], //递延所得税负债 
          'qtfldfz' => $excel[26][4], //其他非流动负债
          'fldfzhj' => $excel[27][4], //非流动负债合计
          'fuzhaiheji' => $excel[28][4], //负债合计

          'sszbhgb' => $excel[30][4], //实收资本(或股本)
          'zbgjj' => $excel[31][4], //资本公积
          'zhuanxiangchubei' => $excel[32][4], //其他综合收益
          'yygjj' => $excel[33][4], //盈余公积
          'wfplr' => $excel[34][4], //未分配利润
          'ssgdqy' => $excel[35][4], //所有者权益（或股东权益）合计
          'syzqyhj' => $excel[37][4], //负债和所有者权益（或股东权益）总计
          


      ];
      //   ajax_return($form_data);

      $alldata = [
          'ok' => 1,
          'form_data' => $form_data
      ];
      ajax_return($alldata);
  }

  /**
   * 上获取excel表数据 VC-JSR演示空间使用
   * $objPHPExcel PHPExcel加载的excel对象数据
   * $index 获取第n个sheet表数据，n从0开始
  */
  public function get_excel_data($objPHPExcel, $index)
  {
      $sheet = $objPHPExcel->getSheet($index);
      $highestRow = $sheet->getHighestRow();
      $highestColumn = $sheet->getHighestColumn();

      $excel = [];
      for ($row = 1; $row <= $highestRow; $row++) {
          $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);

          foreach ($rowData[0] as $k => $v) {
              $excel[$row][$k] = $v;
          }
      }
      return $excel;
  }

  /**
   * 驾驶舱项目分析计算脚本更改
   * @task    2022-05035 https://oa.vc800.com/?/task/view/about/h2nr2x5yiz
   * 需求变更：项目分析-投后履约取数为同一项目取最新数据
   * @task    2022-05050 https://oa.vc800.com/?/task/view/about/h2p1gpzo39
   * 需求优化：符合预期项目占比和低于预期项目占比计算调整
   * @task    2022-05985 
   * @param   string    $value [description]
   * @author  向雨 <yxiang@pepm.com.cn>
   * @version release/v6.2
  */
  function driver_cal(){
    $em = $this->em;
    $data = $em->get(" AND `del`=0 AND `type` = 'jsc'");
    $driver_data = _decode($data[0]['data']);
    $qyrzqk = $em->get(" AND `del`=0 AND `type` = 'qyrzqk'");
    $company = $em->get(" AND `del`=0 AND `type` = 'company'");
    $yjdd = $em->get(" AND `del`=0 AND `type` = 'yjdd'");

    $temp = [];

    foreach($yjdd as $k => $v){
      $temp1 = _decode($v['data']);
      array_push($temp, $temp1);
    }

    //相同的项目取最新的一组数据
    for($i = 0; $i < count($temp); $i++){
      //dump($yjdd);
      for($j = $i+1; $j < count($temp); $j++){
        if($temp[$i]['name_label'] == $temp[$j]['name_label']) {
          if($temp[$i]['h2endfmi5u'] > $temp[$j]['h2endfmi5u']) unset($yjdd[$j]);
          else unset($yjdd[$i]);
        }
      }
    }

    $yjdd = array_merge($yjdd);
    
    $already_ipo = 0;
    $hope_as = 0;
    $hope_low = 0;
    $invested = 0;
    $round = [];
    $online = 0;//已废弃在管
    $test = 0;//在管
    
    foreach($qyrzqk as $k => $v){
      $qyrzqk_data = _decode($v['data']);
      array_push($round, $qyrzqk_data['name_label']);
    }

    $round_after = array_flip($round);
    
    foreach($company as $k => $v){
      $company_data = _decode($v['data']);
      if(($company_data['state'] == '全部退出') || ($company_data['state'] == '退出') || ($company_data['state'] == '投后')) {
        //已投项目
        $invested++;
        if(($company_data['state'] == '投后') || ($company_data['state'] == '退出')){
          $test++;
        }
        //ipo数量
        if($company_data['h0broq8iej'] == '已上市'){
          $already_ipo++;
        }
        foreach($yjdd as $a => $b){
          $yjdd_data = _decode($b['data']);
          //是否符合预期
          if(($company_data['state'] == '投后') || ($company_data['state'] == '退出')){
            if($company[$k]['uuid'] == $yjdd_data['name']){
              $online++;
              if($yjdd_data['h0rnd1ylfy'] == '未触发业绩对赌/回购'){
                if($yjdd_data['h0rns8o0sc'] == '符合预期') $hope_as++;
                else $hope_low++;
              }
              else if($yjdd_data['h0rnd1ylfy'] == '已触发业绩对赌/回购'){
                if($yjdd_data['h0rpffwiok'] == '已触发') $hope_low++;
              }
              else if($yjdd_data['h0rnd1ylfy'] == '已上会完成投资条款调整'){
                if($yjdd_data['h0rnsyv93i'] == '调整后符合预期') $hope_as++;
                else $hope_low++;
              }
              else if($yjdd_data['h0rnd1ylfy'] == '未设置业绩对赌/回购'){
                if($yjdd_data['h215uieku8'] == '符合预期') $hope_as++;
                else $hope_low++;
              }
            }
          }
        }
      }
    }

    //驾驶舱数据计算
    $driver_data['h1on64gqt2'] = $invested;//已投项目数
    $driver_data['h1onha3pwr'] = count($round_after);//后轮融资数
    $driver_data['h2a3kmtbfs'] = $already_ipo;//已上市项目数
    $driver_data['h1onazvm07'] = $hope_as / $test * 100;//符合预期项目占比
    $driver_data['h1onh9t2lb'] = $hope_low / $test * 100;//低于预期项目占比
    $driver_data['h1on6521l0'] = $driver_data['h1onha3pwr'] / $driver_data['h1on64gqt2'] * 100;//后轮融资占比
    $driver_data['h1on7dzx6k'] = $driver_data['h2a3kmtbfs'] / $driver_data['h1on64gqt2'] * 100;//已上市占比
    
    $data[0]['data'] = $driver_data;
    $em->replace($data[0], $data[0]['type'], 'update',false,false,false,false );
  }

    /**
     * 驾驶舱-基金分析-年均收益率计算
     *
     * @task    2022-04793 https://oa.vc800.com/?/task/view/about/h2h2vkdtyo
     
    *需求变更：项目分析-投后履约取数为同一项目取最新数据
    *@task    2022-05050 https://oa.vc800.com/?/task/view/about/h2p1gpzo39
       
     * @param   string    $value [description]
     * @author  向雨 <@pepm.com.cn>
     * @version release/v6.2
     */
    function jscdd_cal($eid){
      
      $em = $this->em;
      $mod = $em->get_one($eid);
      $data = _decode($mod['data']);
      
      //不同入口，取得基金id
      switch($mod['type']){
        case 'company':
        case 'management':
        case 'yjdd':
          $uuid = $data['fund'];
          break;
        default:
          $uuid = $eid;
      }
      
      $company = $em->get(" AND `del`=0 AND `type` = 'company' AND `data`->>'$.fund' = '$uuid'");
      
      $yjdd = $em->get(" AND `del`=0 AND `type` = 'yjdd'");
      $temp = [];
      foreach($yjdd as $k => $v){
        $temp1 = _decode($v['data']);
        array_push($temp, $temp1);
      }
      //相同的项目取最新的一组数据
      for($i = 0; $i < count($temp); $i++){
        //dump($yjdd);
        for($j = $i+1; $j < count($temp); $j++){
          if($temp[$i]['name_label'] == $temp[$j]['name_label']) {
            if($temp[$i]['h2endfmi5u'] > $temp[$j]['h2endfmi5u']) unset($yjdd[$j]);
            else unset($yjdd[$i]);
          }
        }
      }
      $yjdd = array_merge($yjdd);
      
      $hope_as = 0;
      $hope_low = 0;
      $online = 0;
      foreach($company as $k => $v){
        
        $company_data = _decode($v['data']);
        
        if(($company_data['state'] == '投后') || ($company_data['state'] == '退出')){ 
          //在管数目
          $online++;
          
          foreach($yjdd as $a => $b){
            $yjdd_data = _decode($b['data']);
            //dump($yjdd_data);
            if($company[$k]['uuid'] == $yjdd_data['name']){
              //dump($yjdd_data['name_label']);
              if($yjdd_data['h0rnd1ylfy'] == '未触发业绩对赌/回购'){
                if($yjdd_data['h0rns8o0sc'] == '符合预期') $hope_as++;
                else $hope_low++;
               }
              else if($yjdd_data['h0rnd1ylfy'] == '已触发业绩对赌/回购'){
                if($yjdd_data['h0rpffwiok'] == '已触发') $hope_low++;
                }
              else if($yjdd_data['h0rnd1ylfy'] == '已上会完成投资条款调整'){
                if($yjdd_data['h0rnsyv93i'] == '调整后符合预期') $hope_as++;
                else $hope_low++;
               }
              else if($yjdd_data['h0rnd1ylfy'] == '未设置业绩对赌/回购'){
                if($yjdd_data['h215uieku8'] == '符合预期') $hope_as++;
                else $hope_low++;
               }
             }
           }
         }
       }
      
      $jscdd = $em->get_one($uuid);
      $jscdd_data = _decode($jscdd['data']);
      
      $jscdd_data['g1055dy2in'] = $online;//在管
      $jscdd_data['h1onlog60h'] = $hope_as;//符合
      $jscdd_data['h1onlngwgs'] = $hope_low;//低于
      $jscdd_data['h1onmcn8ol'] = $hope_as / $jscdd_data['g1055dy2in'] * 100;//符合占比
      $jscdd_data['h1onlnv6mx'] = $hope_low / $jscdd_data['g1055dy2in'] * 100;//低于占比
      
      $jscdd['data'] = $jscdd_data;
      $em->replace($jscdd, $jscdd['type'], 'update',false,false,false,false );
    }
  /**
     * 驾驶舱-基金分析-IRR计算
     *
     * @task    2022-04792 https://oa.vc800.com/?/task/view/about/h2h2ptnokv
     * @param   string    $value [description]
     * @author  龙大胜 <@pepm.com.cn>
     * @version release/v6.2
   */

  function fund_IRR($fund_uuid){
    if(!$fund_uuid) return;
    $money=[];
    $time=[];

    //通过uuid查找符合条件的投资人现金流数据
    $cashflow_customer=$this->em->get(" and del=0 and type='cashflow_customer' and data->>'$.fullname'='$fund_uuid' ");
    foreach ($cashflow_customer as $k => $v) {
        $v["data"]=_decode( $v["data"]);
        $cf_type=$v["data"]["cf_type"];
        switch ($cf_type) {
            case '(流出)分配本金':
                $money[]=floatval($v["data"]["amount"]);
                $time[]=strtotime($v["data"]["date"]);
                break;
            case '(流出)分配收益':
                $money[]=floatval($v["data"]["amount"]);
                $time[]=strtotime($v["data"]["date"]);
                break;
            case '(流出)其他':
                $money[]=floatval($v["data"]["amount"]);
                $time[]=strtotime($v["data"]["date"]);
                break;
            case '(流出)清算':
                $money[]=floatval($v["data"]["amount"]);
                $time[]=strtotime($v["data"]["date"]);
                break;
            case '(流入)合伙人出资':
                $money[]=-floatval($v["data"]["amount"]);
                $time[]=strtotime($v["data"]["date"]);
                break;
        }
       
    }
    //通过uuid查找符合条件的最新的资产负债表数据
    $fund_jjcb_fzb=$this->em->get(" and del=0 and type='fund_jjcb_fzb' and data->>'$.name'='$fund_uuid' order by data->>'$.h2kf869w59' desc ")[0];
    $fund_jjcb_fzb["data"]=_decode( $fund_jjcb_fzb["data"]);
    //取出所有者权益（或股东权益）合计，并把元转换成万
    $money[]=floatval($fund_jjcb_fzb["data"]["ssgdqy"]/10000);
    //取报表时间
    $time[]=strtotime($fund_jjcb_fzb["data"]["h2kf869w59"]);

    //查出项目数据
    $fund_data=$this->em->get_one($fund_uuid);
    $fund_data["data"]=_decode($fund_data["data"]);
   
    //计算IRR
    $irr = load('lib/financial')->XIRR($money, $time);
    $fund_data["data"]["h24ak0xaqk"]=number_format($irr*100,2);
    $this->em->replace_bare($fund_data);
  }

    /**
     * ​管道内项目初筛对应项目池初筛
     *
     * @task    2022-05042 https://oa.vc800.com/?/task/view/about/h2o3mcft51
     * @param   string    $value [description]
     * @author  龙大胜 <@pepm.com.cn>
     * @version release/v6.2
   */
   function statistical_quantity($eid){
        if(!$eid) return;
        //初筛数量
        $count=0;
        //投委会数量
        $twh_count=0;
        //立项数量
        $lx_count=0;
        //内核会数量
        $nhh_count=0;
        $company_data=$this->em->get(" and del=0 and type='company' ");
        foreach ($company_data as $key => $value) {
            $value["data"]=_decode( $value["data"]);
            $state= $value["data"]["state"];
            switch ($state) {
                case '初筛':
                    $count++;
                    break;
                case '立项':
                    $lx_count++;
                    break;
                case '投委会':
                    $twh_count++;
                    break;
                case '内核会':
                    $nhh_count++;
                    break;
            }
        }
        $jsc=$this->em->get(" and del=0 and type='jsc' ")[0];
        $jsc["data"]=_decode( $jsc["data"]);
        $jsc["data"]["h1onyabonc"]=$count;
        $jsc["data"]["h1onyaj0za"]=$lx_count;
        $jsc["data"]["h1onyarzzr"]=$nhh_count;
        $jsc["data"]["h1onyao4uq"]=$twh_count;
        $this->em->replace_bare($jsc);
    }
   
    /**
     * 基金管理中估值基准日和基金估值提取基金财报资产负债表里字段
     *
     * @task    2022-05036 https://oa.vc800.com/?/task/view/about/h2nr51hxt4
     * @param   string    $value [description]
     * @author  龙大胜 <@pepm.com.cn>
     * @version release/v6.2
    */
    function fund_assignment($fund_uuid){
        if(!$fund_uuid) return;
        //查找当前项目下所有的资产负债表
        $sql=" and del=0 and type='fund_jjcb_fzb' and data->>'$.name'='$fund_uuid' order by data->>'$.h2kf869w59' desc";
        $fund_jjcb_fzb=$this->em->get($sql)[0];
        $fund_jjcb_fzb["data"]=_decode( $fund_jjcb_fzb["data"]);
        //取资产负债表时间
        $time=$fund_jjcb_fzb["data"]["h2kf869w59"];
        //取资产负债表所有者权益（或股东权益）合计
        $money=$fund_jjcb_fzb["data"]["ssgdqy"];

        //查出当前项目
        $fund_data=$this->em->get_one($fund_uuid);
        $fund_data["data"]=_decode( $fund_data["data"]);

        //将资产负债表时间赋给基金管理中的估值基准日
        $fund_data["data"]["h2kf869w59"]=$time;
        //将资产负债表所有者权益（或股东权益）合计赋给基金估值
        $fund_data["data"]["h1o0nzokxb"]=$money/10000;
        $this->em->replace_bare( $fund_data);

    }


   /**
   * 图表>可根据地区控件地段过滤，行业只显示市
   *
   * @task    2022-05037 https://oa.vc800.com/?/task/view/about/h2nrl2v4w9
   * @param   string    $value [description]
   * @author  张汪明 <@pepm.com.cn>
   * @version release/v6.2
   */
    public function updateAddress($companyUUID)
    {

        if (!$companyUUID) return false;

        $company = $this->em->get_one($companyUUID);
        $companyData = _decode($company['data']);

        //$regPlace = $companyData['fyeczs8zqi'];

        if($companyData['fyeczs8zqi']['city']!='合肥市'){
            $companyData['dq']=$companyData['fyeczs8zqi'];
            $companyData['dq']['nation']="";
            $companyData['dq']['province']="";
            $companyData['dq']['district']="";
        }else{
            $companyData['dq']=$companyData['fyeczs8zqi'];
            $companyData['dq']['nation']="";
            $companyData['dq']['province']="";
        }
        
        $company['data'] = $companyData;

        $this->em->replace_bare($company);
    }
    //处理历史数据
    //?/service/vendor_bridge/websid/chuanggu/old_data
    function old_data(){
       $sid=WEBSID;
       $elem=$this->em->get(" and type='company' and del=0 and sid='$sid' ");
       foreach($elem as $v){
          $uuid=$v['uuid'];
          $this->updateAddress($uuid);
       }
    }

  /**
     * 驾驶舱-基金分析-年均收益率计算
     * @task    2022-04793 https://oa.vc800.com/?/task/view/about/h2h2vkdtyo
     * 修改：项目分析-投后履约取数为同一项目取最新数据
     * @task    2022-05050 https://oa.vc800.com/?/task/view/about/h2p1gpzo39
     * 需求变更：基金年均收益率取数变更
     * @task    2022-05169 https://oa.vc800.com/?/task/view/about/h2shrwln9b
     * 优化：驾驶舱基金分析年均收益率增加数值为负值计算
     * @task    2022-05497 https://oa.vc800.com/?/task/view/about/h36dtme5u6
     * @param   string    $value [$eid]
     * @author  向雨 <yxiang@pepm.com.cn>
     * @version release/v6.2
    */
    function jscfund_cal($eid) {
      $em = $this->em;
      $mod = $em->get_one($eid);
      $data = _decode($mod['data']);
      
      //不同入口，取得基金id
      switch($mod['type']) {
        case 'cashflow_customer':
          $uuid = $data['fullname'];
          break;
        case 'cashflow_other_outflow':
          $uuid = $data['name'];
          break;
        case 'fund_jjcb_fzb':
          $uuid = $data['name'];
          break;
        default:
          $uuid = $eid;
      }
      
      //投资人现金流正数
      $cashflow_customer = $em->get(" AND `del`=0 AND `type` = 'cashflow_customer' AND `data`->>'$.fullname' = '$uuid'");
      //投资人现金流负数
      $cashflow_other_outflow = $cashflow_customer;
      //基金财务资产负债表
      $fund_jjcb_fzb = $em->get("AND `del`=0 AND `type`='fund_jjcb_fzb' AND `data`->>'$.name' = '$uuid' order by  `data`->>'$.h2kf869w59' desc",0,1);
      $getdata = _decode($fund_jjcb_fzb[0]['data']);
      $date_new = strtotime($getdata['h2kf869w59']);//最新估值时间
                            
      $cf = [];//现金流数据整合数组

      foreach($fund_jjcb_fzb as $k => $v) {
        $fzb_data = _decode($v['data']);
        $index3 = $fzb_data['name'].'fzb';
        //取正数
        $cf[$index3]['money'][] = $fzb_data['ssgdqy'] / 10000;
        $cf[$index3]['days'][] = (($date_new - strtotime($fzb_data['h2kf869w59'])) /31536000);
      }

      foreach($cashflow_customer as $k => $v) {
        $customer_data = _decode($v['data']);
        $index1 = $customer_data['fullname'].'流出';
        //取正数
        if(($customer_data['cf_type'] == '(流出)分配本金') 
          || ($customer_data['cf_type'] == '(流出)分配收益') 
          || ($customer_data['cf_type'] == '(流出)其他') 
          || ($customer_data['cf_type'] == '(流出)清算')) {
          $cf[$index1]['money'][] = $customer_data['amount'] * 1;
          $cf[$index1]['days'][] = (($date_new - strtotime($customer_data['date'])) /31536000);
        }     
      }

      foreach($cashflow_other_outflow as $k => $v) {
        $other_data = _decode($v['data']);
        $index2 = $other_data['name'].'流入';
        //取负数
        if($other_data['cf_type'] == '(流入)合伙人出资') {
          $cf[$index2]['money'][] = -$other_data['amount'];
          $cf[$index2]['days'][] = (($date_new - strtotime($other_data['date'])) /31536000);
        } 
      }
      
      $sum1 = 0;//投资人正数金额总和
      $sum2 = 0;//投资人正数金额*天数总和
      $sum3 = 0;//投资人负数金额总和
      $sum4 = 0;//投资人负数金额*天数总和
      $sum5 = 0;//负债表金额总和
      $sum6 = 0;//负债表金额*天数总和
      
      foreach($cf[$index1]['money'] as $k => $v){
        $sum1 += $cf[$index1]['money'][$k];
        $sum2 += $cf[$index1]['money'][$k] * $cf[$index1]['days'][$k];
      }
      
      foreach($cf[$index2]['money'] as $k => $v){
        $sum3 += $cf[$index2]['money'][$k];
        $sum4 += $cf[$index2]['money'][$k] * $cf[$index2]['days'][$k];
      }
      
      foreach($cf[$index3]['money'] as $k => $v){
        $sum5 += $cf[$index3]['money'][$k];
        $sum6 += $cf[$index3]['money'][$k] * $cf[$index3]['days'][$k];
      }
      
      $fund = $em->get_one($uuid);
      $fund_data = _decode($fund['data']);
      $fund_data['h24ak0kms3'] = -(($sum1+$sum3+$sum5)/($sum2+$sum4+$sum6) * 100);

      if((is_nan($fund_data['h24ak0kms3'])) 
        ||is_infinite($fund_data['h24ak0kms3'])) {
          $fund_data['h24ak0kms3'] = 0;
      }

      $fund['data'] = $fund_data;
      $em->replace($fund, $fund['type'], 'update', false, false, false, false);
    }

     /**
     * 驾驶舱-基金分析：剩余可投金额限制
     *
     * @task    2022-05227 https://oa.vc800.com/?/task/view/about/h2txpqhph7
     * @param   string    $value [description]
     * @author  龙大胜 <@pepm.com.cn>
     * @version release/v6.2
    */
    
    function state_money($eid){
        if(!$eid) return;
        $sid=WEBSID;
        $fund_data=$this->em->get(" and del=0 and sid='$sid' and type='fund' ");
        foreach ($fund_data as $key => $value) {
            $value["data"]=_decode( $value["data"]);
            $state=$value["data"]["state"];
            //基金实缴规模
            $gyis4h3d73=$value["data"]["gyis4h3d73"];
            //已投资金额
            $fnjjwks5sm=$value["data"]["fnjjwks5sm"];
            //管理费出
            $h19jwbib20=$value["data"]["h19jwbib20"];
            //服务费出
            $h19jwbsb6f=$value["data"]["h19jwbsb6f"];
            //其他出
            $h19jwcb2rq=$value["data"]["h19jwcb2rq"];
            //会务费出
            $h19jxfosd0=$value["data"]["h19jxfosd0"];
            //手续费出
            $h19jxfrgas=$value["data"]["h19jxfrgas"];
            //税费出
            $h19jxfvnxj=$value["data"]["h19jxfvnxj"];
            //交易费用出
            $h1jhgc5dpw=$value["data"]["h1jhgc5dpw"];
            //基金管理费入
            $h1jhikjubz=$value["data"]["h1jhikjubz"];
            //利息入
            $h19jxfzjb5=$value["data"]["h19jxfzjb5"];
            //其他入
            $h19k0bul4y=$value["data"]["h19k0bul4y"];

            //剩余投资金额
            $h19oz3ychu=$value["data"]["h19oz3ychu"]=$gyis4h3d73-$fnjjwks5sm-$h19jwbib20-$h19jwbsb6f-$h19jwcb2rq-$h19jxfosd0-$h19jxfrgas-$h19jxfvnxj-$h1jhgc5dpw+$h1jhikjubz+$h19jxfzjb5+$h19k0bul4y;
            if($state=="退出期" ||$h19oz3ychu<0){
                $value["data"]["h19oz3ychu"]=0;
                $this->em->replace($value,"fund","update",false,false,false,false);
            }else{
                $this->em->replace($value,"fund","update",false,false,false,false);
            }
           
           
        }
    }
  /**
  * task/2022-04353 审批人审批后自动归档文件到审批单中上传
  * task/2022-10335 流程审批单自动归档优化
  * @param string $fundUUID fund uuid
  * @author 张振 <zzhang@pepm.com.cn>
  * @version v6.2
  */
  public function edit_form($param,$extra,$output = false,$return_path=true){
    //生成pdf缓存文件
    $cstep = $param['step'];  //获取当前节点
    $fentity = $extra['fentity'];
    if (!$fentity) {
      return false;
    }
    $UID = $fentity['uuid'];  //当前流程数据的uuid
    $fdata = $fentity['data'];
    $fconfig = $fdata['flow']['item']; 
    $step_keys = array_keys($fconfig);
    ksort($fdata);
    ksort($fentity['data']);
    $sid = WEBSID;  //空间ID
    $file_type = $fentity['type']; //当前流程表单的类型，属于什么表
    $config_sql = "select uuid from config where sid='$sid' and `key`='$file_type'";
    $file_uuid = load("m/config_m")->db->query($config_sql);  //查出当前是什么表（内核上会，申请表。。。）
    $xmlc_uuid = $file_uuid[0]['uuid'];  //项目流程的uuid
    switch($xmlc_uuid){
      case "h0d1aciac9":
        $xmlc_first_step = 'h0d05xo496';
        break;
      case "gtlutd1sst":
        $xmlc_first_step = 'gtlutne0dq';
        break;
      case "gwb0jibo6j":
        $xmlc_first_step = 'gwb0jqmvph';
        break;
      case "gtlvv8szhk":
        $xmlc_first_step = 'gtlvvebsg5';
        break;
      case "gtlwr2gr4w":
        $xmlc_first_step = 'gtlwr7hbbc';
        break;
      case "gwb1xcak0h":
        $xmlc_first_step = 'gwb1xs7gle';
        break;
      case "gtlx7v31lc":
        $xmlc_first_step = 'gtlx7z2mwp';
        break;
      case "gtlxzw2l56":
        $xmlc_first_step = 'gtlxzz2kx4';
        break;
      case "g4gx5yrnhr":
        $xmlc_first_step = 'flkejtfjio';
        break;
      case "gtm148w29h":
        $xmlc_first_step = 'gtm14fdai8';
        break;
      case "gway1yclvc":
        $xmlc_first_step = 'gway22014j';
        break;
      case "gwchf7tj4j":
        $xmlc_first_step = 'gwchfajasd';
        break;
      default:
        $xmlc_first_step = '';
    }
    $file_key = $xmlc_uuid.'01';   //拼接成要更新的文件字段
    $xm_sql = "select uuid from config where `label` = '项目文档' and sid = '$sid'";
    $xm_uuid = load("m/config_m")->db->query($xm_sql);  //查出项目文档在配置表中的uuid
    $cid = $xm_uuid[0]['uuid'];
    $file_eid = $fdata['eid'];
    $filename = "《".$fentity['name']." ".date("Y-m-d")." 》";
    $co_sign_result = load("c/flow")->get_co_sign_result($fentity, $step_keys, 'print');
    $layout = load('lib/layout');
    $html = load("c/flow")->flow_layout($fentity, $co_sign_result, $step_keys, 'print');
    $layout->filename = $filename;
    $layout->type = 'pdf';
    $layout->rtn = true;
    $out = $layout->init('output',false)->html2office($html);
    $save_name = uuid();  // 保存的文件名以uuid起名字，使用uuid()方法避免同一时间uuid相同
    $cache_path = APP.'cache/'.$save_name.'_office.html';  //html存放路径在app/cache
    $cache_pdf_path = APP.'cache/'.$save_name.'_office.pdf';  //pdf存放路径在app/cache
    file_put_contents($cache_path,$out);
    $type = 'pdf';
    $cmd = "unoconv -f ".$type." ".$cache_path;
    exec($cmd);
    // if(!file_exists($cache_pdf_path))continue;
    //把文件复制到upload里
    $file_uid = 'gwovi00jox';
    $doc_type = $extra['entity_data']['type'];
    $file_path_time = date("Ym",time());
    $nfile_dir = UPLOAD_BASE.WEBSID."/".$file_path_time."/".$save_name;
    $nfile = $nfile_dir."/".$save_name;
    //task/2022-14322 流程自动生成审批单、投后走投前不变更状态、基金已完成返投额度字段脚本失效了 start
    b2mkdir($nfile_dir);
    chmod($nfile_dir,0777);
     //task/2022-14322 流程自动生成审批单、投后走投前不变更状态、基金已完成返投额度字段脚本失效了 end
    $file_size = filesize($cache_pdf_path);
    $upload_time = time();
    $quote = array(
      'mod' => array(
        $UID => array($file_key),
      ),
      'flow' => array(
        $upload_time => $file_key,
      )
    );
    $path = array(
      'config' => array(
        'cid' => $cid,
        'uid' => 'h3fno0gpkm'
      ),
      'eid' => $file_eid
    );
    $data = array(
      'path' => array()
    );
    $file = array(
      'uuid' => $save_name,
      'eid' => $file_eid,
      'file' => $nfile,
      'type' => $type,
      'data' => '[]',
      'uid' =>  'h3fno0gpkm',
      'sid' => $sid,
      'name' => $filename,
      'size' => $file_size,
      'create_people' => 'gzjsbs1vcx',
      'update_people' => 'gzjsbs1vcx',
      'create_date' => $upload_time,
      'update_date' => $upload_time,
      'doc_type' => $doc_type,
      'filecrypt' => 0,
      'del' => 0,
      'quote' => $quote,
      'path' => $path,
      'data' => $data
    );
    copy($cache_pdf_path,$nfile);
    unlink($cache_pdf_path);
    if(!file_exists($nfile)){
      exit;
    }
    load("m/file_m")->add($file);
    $form_info =  load("m/entity_m")->get_one($UID);  //找出这条申请表单的所有数据
    $new_form_info = _decode($form_info['data']);  //将数据data转数组
    if($fdata){
      foreach($fdata as $k => $item){
        if(!in_array($item['step'],$step_keys))continue;  //判定流程在不在流程组中，不在的话终止本次循环
        if(in_array($k,(array)$fdata['retract']))continue;  //判定流程在不在撤销中，在的话终止本次循环
        if(in_array($k,(array)$fdata['reject_array']))continue;  //判定流程在不在驳回中，在的话终止本次循环
        if($item['step'] == $cstep)   continue;  //判定流程是不是当前流程，是的话终止本次循环
        if($item['step'] == $xmlc_first_step){    //判定剩余流程中哪个是发起流程，更新发起流程中的数据
          if($item['data']['name_label']){
            $company_name = $item['data']['name_label'];
          }else{
            $company_name = $item['data']['name'];
          }
          $file_name = "《".$fentity['name']." ".$company_name.' '.date("Y-m-d")." 》".'.'.$type;  //文件名称带后缀名
          $new_form_info[$k]['data'][$file_key.'_label'] = array('',$file_name);  //替换成最新的label
          $new_form_info[$k]['data'][$file_key] = array('',$save_name);        //替换成最新文件名称
          load("m/entity_m")->replace(["uuid" => $UID,"data" => $new_form_info]);  
          break;       
        } 
      }
    }             
  }
    
    /**
     * 计算已投项目费用及税金
     *
     * @task    2022-05336 https://oa.vc800.com/?/task/view/about/h2zqi04ps2
     * @param   string    $value [description]
     * @author  龙大胜 <@pepm.com.cn>
     * @version release/v6.2
    */
    function calculate_expenses($eid){
        if(!$eid) return;
        //获取到当前项目
        $company_data=$this->em->get($eid);
        $company_data["data"]=_decode($company_data["data"]);
        //(流出)交易费用金额
        $outflow_expense_money=0;
        //(流入)交易费用金额
        $inflow_expense_money=0;
        //(流出)增值税销项税
        $utflow_zzs_money=0;
        //(流出)中介服务费
        $agency_money=0;
        //(流出)诉讼仲裁费
        $litigation_arbitration_money=0;
        //获取项目状态
        $state=$company_data["data"]["state"];
        if($state=="打款交割"||$state=="投后"||$state=="退出"||$state=="全部退出"){
            //获取到当前项目下所有的现金流信息
            $cashflow_portfolio=$this->em->get(" and del=0 and type='cashflow_portfolio' and data->>'$.company'='$eid' ");
            foreach ($cashflow_portfolio as $key => $value) {
                $value["data"]=_decode($value["data"]);
                $cf_type=$value["data"]["cf_type"];
                switch ($cf_type) {
                    case '(流出)交易费用':
                        $outflow_expense_money+=$value["data"]["fukuanjine"];
                        break;
                    case '(流入)交易费用':
                        $inflow_expense_money+=$value["data"]["fukuanjine"];
                        break;
                    case '(流出)中介服务费':
                        $agency_money+=$value["data"]["fukuanjine"];
                        break;
                    case '(流出)诉讼仲裁费':
                        $litigation_arbitration_money+=$value["data"]["fukuanjine"];
                        break;
                }
            }
            //获取到当前项目下所有的增值税信息
            $cashflow_portfolio=$this->em->get(" and del=0 and type='cashflow_portfolio_zzs' and data->>'$.company'='$eid' ");
            foreach ($cashflow_portfolio as $key => $value) {
                $value["data"]=_decode($value["data"]);
                $utflow_zzs_money+=$value["data"]["fukuanjine1"];
            }
           
            //费用及税金=项目现金流(流出)交易费用-项目现金流(流入)交易费用+(流出)增值税销项税
            $company_data["data"]["h2kpe5u5z8"]=$outflow_expense_money-$inflow_expense_money+$utflow_zzs_money+$agency_money+$litigation_arbitration_money;
            $this->em->replace($company_data,"company","update",false,false,false,false);
        }
    }

    /**
     * 投后项目走投前流程，保持项目状态只许向退出与全部退出状态改变
     *
     * @task    2022-05500 https://oa.vc800.com/?/task/view/about/h36el4sdrw
     * @param   string    $value [description]
     * @author  龙大胜 <@pepm.com.cn>
     * @version release/v6.2
    */
    function judging_process_status($post,$extra){
        //查找项目
        $company_uuid=$extra["entity_data"]["uuid"];
        $company_data=$this->em->get($company_uuid);
        $company_data["data"]=_decode($company_data["data"]);
        $state=$extra["entity_data"]["data"]["state"];
        //如果是投后，状态不变
        if($state=="投后"){
            $company_data["data"]["state"]=$company_data["data"]["state_label"]="投后";
        }
        if($state=="退出"){
            $company_data["data"]["state"]=$company_data["data"]["state_label"]="退出";
        }
        
        $this->em->replace($company_data,"company",false,false,false,false);
    }

    /**
     * 驾驶舱已投项目分析增加关联数据过滤
     * @task    2022-05498 https://oa.vc800.com/?/task/view/about/h36e2zlqkt
     * @param   string    $value [$eid]
     * @author  向雨 <yxiang@pepm.com.cn>
     * @version release/v6.2
    */
    function filter_yjdd($eid){
      $em = $this->em;
      $uuid = $eid['eid'];

      $yjdd = $em->get("and `del` = 0 and `type` = 'yjdd' and `data`->>'$.fund' = '$uuid'");
      $company = $em->get(" AND `del`=0 AND `type` = 'company' AND `data`->>'$.fund' = '$uuid'");

      $temp = [];

      foreach($yjdd as $k => $v){
        $temp1 = _decode($v['data']);
        array_push($temp, $temp1);
      }

      //相同的项目取最新的一组数据
      for($i = 0; $i < count($temp); $i++){
        for($j = $i+1; $j < count($temp); $j++){
          if($temp[$i]['name_label'] == $temp[$j]['name_label']){
            if($temp[$i]['h2endfmi5u'] > $temp[$j]['h2endfmi5u']){
                unset($yjdd[$j]);
            }
            else{
                unset($yjdd[$i]);
            } 
          }
        }
      }

      $yjdd = array_merge($yjdd);
      $filter_yjdd = [];

      foreach($company as $k => $v){
        $company_data = _decode($v['data']);
        if(($company_data['state'] == '投后') || ($company_data['state'] == '退出')){ 
          foreach($yjdd as $a => $b){
            $yjdd_data = _decode($b['data']);
            if($company[$k]['uuid'] == $yjdd_data['name']){
              array_push($filter_yjdd, $yjdd[$a]);
            }
          }
        }
      }
      return $filter_yjdd;
    }

    /**
     * 工作日报增加默认抄送逻辑
     *
     * @task    2022-05505 https://oa.vc800.com/?/task/view/about/h36f7fx5nb
     * @param   string    $value [description]
     * @author  龙大胜 <@pepm.com.cn>
     * @version release/v6.2
    */
    function default_cc(){
        $sid = WEBSID;
        $uid=$_POST["kid"];
        //获取到当前用户信息
        $space_usr=$this->space_usr_m->get(" and del=0 and sid='$sid' and uid='$uid' ")[0];
        $space_usr["data"]=_decode( $space_usr["data"]);
        $structure_label= $space_usr["data"]["structure_label"];  
        $array=[];
        //如果日报填写人为牛阳只通知钱进
        if(in_array("公司领导",$structure_label)&&$space_usr["data"]["name"]=="牛阳"){
            $space_data=$this->space_usr_m->get(" and del=0 and sid='$sid' and data->>'$.name' in ('钱进') ");
            foreach ($space_data as $key => $value) {
                //获取要发送的人的uid
                $array[]=$value["uid"];
            }
            ajax_return ($array);
        }
        //判断是公司领导
        if(in_array("公司领导",$structure_label)&&$space_usr["data"]["name"]!="钱进"){
            $space_data=$this->space_usr_m->get(" and del=0 and sid='$sid' and data->>'$.name' in ('钱进','牛阳') ");
            foreach ($space_data as $key => $value) {
                //获取要发送的人的uid
                $array[]=$value["uid"];
            }
            ajax_return ($array);
        }
        //如果不是公司领导
        if(!in_array("公司领导",$structure_label)) {
            $space_data=$this->space_usr_m->get(" and del=0 and sid='$sid' and data->>'$.name' in ('钱进','牛阳','涂振欣','李嘉煦','王新鸣') ");
            foreach ($space_data as $key => $value) {
                $array[]= $value["uid"];
            }
            ajax_return($array);

        }
    }

             /**
     * 创谷工作周报修改
     * @task    2022-05721 https://oa.vc800.com/?/task/view/about/h3b11ffgp9
     * @param   string    $value [$eid]
     * @author   张振 <zzhang@pepm.com.cn>
     * @version release/v6.2
    */
    public function addWeekAll($data,$eid)
    {   
       if($data&&$eid){
        $data = _decode($data);
        $weekTime = $data['weeknum'];  //填写的年份和周次
        $department = $data['h1at5et356'];  //填写的用户属于哪个部门
        $department_name = $data['h1at5et356_label'];
        $getDateStr = function($weekTime) {
            $year = intval(substr($weekTime,0,4));                          // 年份
            $weeks = intval(substr($weekTime, strlen($weekTime) - 2));      // 周次
            // 根据1月1日计算当前年第一周的第一天
            $firstDay = 1;
            $week = intval(date('N', strtotime($year . '-01-' . $firstDay)));
            if ($week !== 1) {
                $firstDay += 7 - $week + 1;
            }
            $time = strtotime($year . '-01-' . $firstDay);
            $day = 86400;       // 一天的秒数
            // 计算出目标周次的区间
            $time += $day * ($weeks - 1) * 7;       // 第一天
            $timeEnd = $time + $day * 6;            // 最后一天

            return date("Y-m-d", $time) . ' 至 ' . date("Y-m-d", $timeEnd);
        };
       
        $date = $getDateStr($weekTime);  //获取期间备注
        $info = $this->em->get_one($eid);
        $info['data'] = _decode($info['data']);
        $info['data']['h3h01moi18'] = $date;
        $this->em->replace_bare($info);   //将期间备注更新至数据中
         //添加周报之后，判断对应的部门有没有记录，如果没有的话新增一条部门记录
        $department_info = $this->em->get("AND `del` = 0 AND `type` = 'department' AND `data`->>'$.h3h427dmd7' = '$department'");
        if (!$department_info) {
            $department_elem = [       //工作部门汇总数据
                'type' => 'department',
                'data' => [
                    'h3h427dmd7' => $department,     //部门
                    'h3h427dmd7_label' => $department_name,
                    'h3i5enx04g' =>array($weekTime)
                ]
            ];
            $this->em->add($department_elem);
        }
      }
    }


        /**
     * 已投项目：当前投资轮次从（后轮融资里面带过来）
     * @task    2022-06097 https://oa.vc800.com/?/task/view/about/h3mktzmmz5
     * @param   string    $value [$eid]
     * @author   张振 <zzhang@pepm.com.cn>
     * @version release/v6.2
    */
     public function update_rzqk($eid)
    {   
       if($eid){
        $info = $this->em->get_one($eid);   //这条融资情况的数据
        $info['data'] = _decode($info['data']);  
        $company_info = $this->em->get_one($info['data']['name']);  //项目的信息
        $company_info['data'] = _decode($company_info['data']); 
        if($info['data']['h38ylyio6n']){
            $company_info['data']['h38ylyio6n'] = $info['data']['h38ylyio6n'];
            $company_info['data']['h38ylyio6n_label'] = $info['data']['h38ylyio6n_label'];
        }
        $this->em->replace_bare($company_info);   //将当前投资轮次更新至项目数据中
      }
    }


          /**
     * 基金管理：条件1.是否属于特殊基金 为“否”时，剩余任务为负数是，显示为0，正数时，正常显示
     * @task    2022-06095 https://oa.vc800.com/?/task/view/about/h3mk9tv95n
     * @param   string    $value [$eid]
     * @author   张振 <zzhang@pepm.com.cn>
     * @version release/v6.2
    */
    public function update_task_status($fund)
    {   
       
        $sid = WEBSID;
        if($fund){
        $sql = "select * from entity where data->>'$.fund'='$fund' and del=0 and sid='$sid' and type='ztftqkb'";
        $info = $this->em->db->query($sql); //返投信息数据
        $fund_info = $this->em->get_one($fund);  //基金信息
        $fund_info['data'] = _decode($fund_info['data']);
        if($fund_info['data']['special']=='否'){   //代表是普通基金
            if($info){
                foreach($info as $k=>$v){
                    $v['data'] = _decode($v['data']);
                    $last_task = $v['data']['gu3lyu8a6q']-$v['data']['gu3lyukfkz'];  //剩余任务=返投要求-完成额度
                    if($last_task<0){    //如果剩余任务小于0，那么显示为0
                        $v['data']['gu3lyuxasu'] = 0;
                    }
                    $this->em->replace_bare($v);   //将剩余任务更新到返投信息中
                }
            }
        }
      }
    }
    /**
     * 基金管理：条件1.是否属于特殊基金 为“否”时，剩余任务为负数是，显示为0，正数时，正常显示
     * @task    2022-06095 https://oa.vc800.com/?/task/view/about/h3mk9tv95n
     * @param   string    $value [$eid]
     * @author   张振 <zzhang@pepm.com.cn>
     * @version release/v6.2
    */
    public function update_company_task_status($fund)   //修改项目基金为特殊或者普通时候
    {   
       
        $sid = WEBSID;
        if($fund){
        $sql = "select * from entity where data->>'$.fund'='$fund' and del=0 and sid='$sid' and type='ztftqkb'";
        $info = $this->em->db->query($sql); //返投信息数据
        $fund_info = $this->em->get_one($fund);  //基金信息
        $fund_info['data'] = _decode($fund_info['data']);
        if($fund_info['data']['special']=='否'){   //当项目基金从特殊基金变成普通基金时候
            if($info){
                foreach($info as $k=>$v){
                    $v['data'] = _decode($v['data']);
                    $last_task = $v['data']['gu3lyu8a6q']-$v['data']['gu3lyukfkz'];  //剩余任务=返投要求-完成额度
                    if($last_task<0){    //如果剩余任务小于0，那么显示为0
                        $v['data']['gu3lyuxasu'] = 0;
                    }
                    $this->em->replace_bare($v);   //将剩余任务更新到返投信息中
                }
            }
        }else{   //当项目基金从普通基金变成特殊基金时候
            if($info){
                foreach($info as $key=>$val){
                    $val['data'] = _decode($val['data']);
                     //如果剩余任务小于0，那么正常显示
                        $val['data']['gu3lyuxasu'] = $val['data']['gu3lyu8a6q']-$val['data']['gu3lyukfkz'];  //剩余任务=返投要求-完成额度
                    $this->em->replace_bare($val);   //将剩余任务更新到返投信息中
                }
            }
        }
      }
    }
  
  /**
  * 内核会上会根据上会类型来触发钩子
  * @task    2022-08555 https://oa.vc800.com/?/task/view/about/h5a0mhxsih
  * @param   string    $value [$eid]
  * @author   张振 <zzhang@pepm.com.cn>
  * @version release/v6.2
  */
  public function update_entity_nhhsh($param,$extra){
    $cstep = $param['step'];  //获取当前节点
    $fentity = $extra['fentity'];
    if (!$fentity) {
       exit('流程不存在');
    }
    $company_uuid = $extra['entity_data']['uuid']; //项目uuid
    //获取项目的数据
    $company_info = $this->em->get_one($company_uuid);
    $company_info['data'] = _decode($company_info['data']);
    $fdata = $fentity['data'];
    $fconfig = $fdata['flow']['item']; 
    $step_keys = array_keys($fconfig);
    ksort($fdata);
    if($fdata){
      foreach($fdata as $k=>$item){
        if(!in_array($item['step'],$step_keys))continue;  //判定流程在不在流程组中，不在的话终止本次循环
        if(in_array($k,(array)$fdata['retract']))continue;  //判定流程在不在撤销中，在的话终止本次循环
        if(in_array($k,(array)$fdata['reject_array']))continue;  //判定流程在不在驳回中，在的话终止本次循环
        //获取第一步流程时候的数据
        if($item['step'] == $step_keys[0]){
          $gtlu7i6ig9 = $item['data']['gtlu7i6ig9']; //上会类型
          $gtlu7i6ig9_label = $item['data']['gtlu7i6ig9_label']; //上会类型
          //获取跟项目关联的返投数据
          if($gtlu7i6ig9 == '投资'){
            $fantou_info = $this->em->get("AND del = 0 AND type = 'xmftxx_lc' AND data->>'$.name' = '$company_uuid' AND _rel = '".$item['step_uuid']."' ");
            if($fantou_info){
              foreach($fantou_info as $fantou_info_v){
                $fantou_info_v['data']= _decode($fantou_info_v['data']);
                $info['uuid'] = uuid();
                $info['data'] = $fantou_info_v['data'];
                $info['data']['_rel'] = $fantou_info_v['data']['name'];
                $info['data']['_rel_view'] = 'management';
                $info['type'] = 'xmftxx';
                $info['name'] = $fantou_info_v['data']['name_label'];
                $this->em->add($info);
              }
            }
          }
          $h13b2a5o4t = $item['data']['h13b2a5o4t']; //项目开发人员（内部）
          $h13b2a5o4t_label = $item['data']['h13b2a5o4t_label']; //项目开发人员（内部）
          $gwa39zwwsx = $item['data']['gwa39zwwsx']; //责任MD
          $gwa39zwwsx_label = $item['data']['gwa39zwwsx_label']; //责任MD
          $team_fzrB = $item['data']['team_fzrB']; //项目经理B角
          $team_fzrB_label = $item['data']['team_fzrB_label']; //项目经理B角
          $team_fzrA = $item['data']['team_fzrA']; //项目经理A角
          $team_fzrA_label = $item['data']['team_fzrA_label']; //项目经理A角
          $team_xmz = $item['data']['team_xmz']; //项目组员
          $team_xmz_label = $item['data']['team_xmz_label']; //项目组员
          $gwb9v1yren = $item['data']['gwb9v1yren']; //风控
          $gwb9v1yren_label = $item['data']['gwb9v1yren_label']; //风控
          $gwb9v29a78 = $item['data']['gwb9v29a78']; //法务
          $gwb9v29a78_label = $item['data']['gwb9v29a78_label']; //法务
          $nitoujine = $item['data']['nitoujine']; //拟投金额
          $nitoujine_label = $item['data']['nitoujine_label']; //拟投金额
          $fund1 = $item['data']['fund1']; //所属基金
          $fund1_label = $item['data']['fund1_label']; //所属基金
          $gwa4uimt1w = $item['data']['gwa4uimt1w']; //投资建议书
          $gwa4uimt1w_label = $item['data']['gwa4uimt1w_label']; //投资建议书
          $gtluqp0nls = $item['data']['gtluqp0nls']; //关键条款
          $gtluqp0nls_label = $item['data']['gtluqp0nls_label']; //关键条款
        }
        if($item['step'] == $step_keys[2]){
          $h2wl54bnro = $item['data']['h2wl54bnro']; //财务尽调报告
          $h2wl54bnro_label = $item['data']['h2wl54bnro_label']; //财务尽调报告
        }
        if($item['step'] == $step_keys[3]){
          $h2wlbn7gsa = $item['data']['h2wlbn7gsa']; //法律尽调报告
          $h2wlbn7gsa_label = $item['data']['h2wlbn7gsa_label']; //法律尽调报告
          $h2wlbo3ct4 = $item['data']['h2wlbo3ct4']; //风控报告
          $h2wlbo3ct4_label = $item['data']['h2wlbo3ct4_label']; //风控报告
        }
        //判定流程是不是当前流程
        if($item['step']==$cstep){
          //再判定当前流程中上会类型是不是投资，如果是投资进行更新操作
          if($gtlu7i6ig9 == '投资'){
            $company_info['data']['gwa39zwwsx'] = $gwa39zwwsx;
            $company_info['data']['gwa39zwwsx_label'] = $gwa39zwwsx_label;
            $company_info['data']['team_fzrB'] = $team_fzrB;
            $company_info['data']['team_fzrB_label'] = $team_fzrB_label;
            $company_info['data']['team_fzrA'] = $team_fzrA;
            $company_info['data']['team_fzrA_label'] = $team_fzrA_label;
            $company_info['data']['team_xmz'] = $team_xmz;
            $company_info['data']['team_xmz_label'] = $team_xmz_label;
            $company_info['data']['gwb9v1yren'] = $gwb9v1yren;
            $company_info['data']['gwb9v1yren_label'] = $gwb9v1yren_label;
            $company_info['data']['gwb9v29a78'] = $gwb9v29a78;
            $company_info['data']['gwb9v29a78_label'] = $gwb9v29a78_label;
            $company_info['data']['nitoujine'] = $nitoujine;
            $company_info['data']['nitoujine_label'] = $nitoujine_label;
            $company_info['data']['fund1'] = $fund1;
            $company_info['data']['fund1_label'] = $fund1_label;
            $company_info['data']['gwa4uimt1w'] = $gwa4uimt1w;
            $company_info['data']['gwa4uimt1w_label'] = $gwa4uimt1w_label;
            $company_info['data']['gtluqp0nls'] = $gtluqp0nls;
            $company_info['data']['gtluqp0nls_label'] = $gtluqp0nls_label;
            $company_info['data']['h2wl54bnro'] = $h2wl54bnro;
            $company_info['data']['h2wl54bnro_label'] = $h2wl54bnro_label;
            $company_info['data']['h2wlbn7gsa'] = $h2wlbn7gsa;
            $company_info['data']['h2wlbn7gsa_label'] = $h2wlbn7gsa_label;
            $company_info['data']['h2wlbo3ct4'] = $h2wlbo3ct4;
            $company_info['data']['h2wlbo3ct4_label'] = $h2wlbo3ct4_label;
            $company_info['data']['h13b2a5o4t'] = $h13b2a5o4t;
            $company_info['data']['h13b2a5o4t_label'] = $h13b2a5o4t_label;
            $this->em->replace_bare($company_info);
          }
        } 
      }
    }    
  }

 /**
  * 流程中心显示项目名称/流程名称   流程中心显示文件名称     
  * @task    2022-09032 https://oa.vc800.com/?/task/view/about/h5k78khuhu
  * @param   
  * @author  孙昊 <hsun@pepm.com.cn>
  * @version release/v6.2
  */
  public function cctwo($param, $extra) {
    $sid = WEBSID;
    $file_name = array();
    $file_name_uuid = $extra['post_data']['step_uuid'];
    $sql = "SELECT * FROM `entity` WHERE sid = '$sid' AND type = 'yymx' AND _rel = '$file_name_uuid'";
    $info = $this -> em -> db -> query($sql);
    foreach ($info as $key => $value) {
      $value[$key]['data'] = _decode($value['data']);
      $file_name[] = $value[$key]['data']['h22a34rxwr'];
    }
    $extra['post_data']['data']['h4memct4q3'] = implode(",", $file_name);
    $elem = load("m/entity_m") -> get_one($param['eid']);
    $elem['data'] = _decode($elem['data']);
    //task/2022-14518 流程第二节点不同意后，发起人撤回，会出现表单消失问题 start
    //不能把pre_config字段覆盖掉，此字段应用于撤回后的表单记录
    $extra['post_data']['pre_privilege'] = $elem['data'][$elem['update_date']]['pre_privilege'];
    $extra['post_data']['pre_config'] = $elem['data'][$elem['update_date']]['pre_config'];
    //task/2022-14518 流程第二节点不同意后，发起人撤回，会出现表单消失问题 end
    $elem['data'][$elem['update_date']] = $extra['post_data'];
    load("m/entity_m") -> replace($elem);
  }

  /**
  * 剩余返投任务脚本计算
  * @task    2022-09602 https://oa.vc800.com/?/task/view/about/h5xs1czvtn
  * @param   
  * @author  张振 <zzhang@pepm.com.cn>
  * @version release/v6.2
  */
  public function update_zyed($eid) {
    if(!$eid){
      return false;
    }
    $sid = WEBSID;
    $fund_info = $this->em->get($eid);
    $fund_info['data'] = _decode($fund_info['data']);
    $total_value = $fund_info['data']['h19oz1iryn'] - $fund_info['data']['h19oz1wmpo'];
    $fund_info['data']['h02fe1npf7'] =  $fund_info['data']['h19oz3ychu'] - $total_value;
    $fund_info['data']['h02fe1gkev'] = $total_value;
    if($fund_info['data']['h02fe1npf7'] < 0){
      $fund_info['data']['h02fe1npf7'] = 0;
    }
    $this->em->replace_bare($fund_info);
  }

  /**
  * 工作日报默认抄送为uuid错误
  * @task    2022-10745  https://oa.vc800.com/?/task/view/about/h6opns9qh6
  * @param   
  * @author  张振 <zzhang@pepm.com.cn>
  * @version release/v6.2
  */
  public function update_usr_label($eid){
    $sid = WEBSID;
    if(!$eid){
      return false;
    }
    $gzrb = $this->em->get($eid);
    $gzrb['data'] = _decode($gzrb['data']);
    if(!$gzrb['data']['h2dkb6jwil_label']){
      $new_uuid = explode(',',$gzrb['data']['h2dkb6jwil']);
      $arr = array();
      $space_data = $this->space_usr_m->get(" and del=0 and sid='$sid' and  `uid` in ('" . implode("','", $new_uuid) . "')");
      foreach ($space_data as $key => $value) {
          $value = _decode($value['data']);
          //获取要发送的人的名称
          $arr[] = $value["name"];
      }
      $gzrb['data']['h2dkb6jwil_label'] = implode('，',$arr);
      $this->em->replace_bare($gzrb);
    }
  }

  /**
  * 返投情况状态根据概况状态进行变更，已完成返投额度只计算投后、退出、完成退出状态
  * @task    2022-14656 返投情况状态根据概况状态进行变更，已完成返投额度只计算投后、退出、完成退出状态  https://oa.vc800.com/?/task/view/about/h94cjhyh8l
  * @param   
  * @author  张振 <zzhang@pepm.com.cn>
  * @version release/v6.2
  */
  public function update_company_gl_status($param,$extra){
    //获取项目的uuid
    $company_uuid = $extra['entity_data']['uuid'];
    //获取项目信息
    $company_info = $this->em->get($company_uuid);
    $company_info['data'] = _decode($company_info['data']);
    //根据项目uuid查询对应的项目返投信息
    $xmftxx_info = $this->em->get(" AND type = 'xmftxx' AND del = 0 AND data->>'$.name' = '$company_uuid'");
    if($xmftxx_info){
      foreach($xmftxx_info as $xmftxx_info_v){
        $xmftxx_info_v['data'] = _decode($xmftxx_info_v['data']);
        $xmftxx_info_v['data']['state'] = $company_info['data']['state'];
        $xmftxx_info_v['data']['state_label'] = $company_info['data']['state_label'];
        $this->em->replace_bare($xmftxx_info_v);
      }
    }
    $this->summaryBackInvest($company_info['data']['fund']);
    $this->summaryBackInvestAmount($company_info['data']['fund']);
  }

  /**
  * 创谷估值：4种系统内部是数据取数估值方法开发
  * @task    2024-11694   https://oa.vc800.com/?/flow/view/hsxy156hwc
  * @param   
  * @author  陈延阳 <yychen@pepm.com.cn>
  * @version release/v7.1 
  */
  function create_guzhi()
  {
    set_time_limit(0);
    $uuid = $_POST['uuid'];
    $nian = $_POST['time'];
    $jidu = $_POST['jidu'];
    $jizhunri = str_replace('/','-',$_POST['jizhunri']);// 基准日
    $api = load('c/api');
    // dump($uuid);
    // dump($nian);
    // dump($jidu);
    // dump($jizhunri);
    // 计算估值
    // 获取 xiangmuguzhi 配置
    $xiangmuguzhi_config = $this->cm->key('xiangmuguzhi')['item'];
    // 获取 对应uuid 的数据
    $uuid_str = implode("','",$uuid);
    // dump($uuid_str);
    $company = $this->em->get("AND `uuid` in ('{$uuid_str}') AND `type` = 'company' AND `del` = 0");
    // $company = $this->em->get("AND `uuid` = '{$uuid}' AND `type` = 'company' AND `del` = 0");
    // dump($company);
    $config = array_keys($xiangmuguzhi_config);
    
    foreach ($company as $key => $value) {
      $arr = $data = array();
      $value['data'] = _decode($value['data']);
      $value_key = array_keys($value['data']);
      foreach ($config as $k => $v) {
        if(in_array($v,$value_key)){
          $arr[$v] = $value['data'][$v];
          if($xiangmuguzhi_config[$v]['type'] == 'select_new'){
            $arr[$v.'_label'] = $value['data'][$v.'_label'];
          }
        }
      }
      $arr['h1o0ncqdho'] = $jizhunri;
      $arr['gznf'] = $nian;
      $arr['gzjd'] = $arr['gzjd_label'] = $jidu;
      $arr['screenName'] = $value['data']['fullname'];
      $arr['company'] = $value['uuid'];
      $arr['company_label'] = $value['data']['name'];  
      // 获取上次的估值公允价值
      $pre_gz = $this->em->get("AND `type` = 'xiangmuguzhi' AND `del` = 0 AND `data`->>'$.company' = '{$arr['company']}' AND `data`->>'$.fund' = '{$arr['fund']}' order by `data`->>'$.h1o0ncqdho' desc")[0];
      $arr['bcgzqgyjz'] = _decode($pre_gz['data'])['gfpgl2jx71']; // 本次估值前公允价值 bcgzqgyjz
      $cashflow_portfolio = $this->em->get("AND `type` = 'cashflow_portfolio' AND `del` = 0 AND `data`->>'$.company' = '{$value['uuid']}' AND `data`->>'$.fund' = '{$value['data']['fund']}' AND `data`->>'$.fukuanshijian' <= '{$jizhunri}'");
      $arr['fvf2axxeoo'] = 0;
      $arr['gwftl5bgwl'] = 0;
      $arr['h24b9vxbed'] = 0;
      $arr['gtm4p6u9b5'] = 0;
      $arr['xmzsr'] = 0;
      foreach ($cashflow_portfolio as $k => $v) {
        $v['data'] = _decode($v['data']);
        switch ($v['data']['cf_type']) { 
          case '(流出)投资款':$arr['fvf2axxeoo'] += $v['data']['fukuanjine']; break;
          case '(流入)退出款':
            $arr['gwftl5bgwl'] += $v['data']['h117obgb3r']; 
            $arr['gtm4p6u9b5'] += $v['data']['h117ocajr7'];
            $arr['xmzsr'] += $v['data']['fukuanjine']; 
            break;
          case '(流入)持有期间收益':$arr['xmzsr'] += $v['data']['fukuanjine']; break;
          case '(流入)其他收益':$arr['xmzsr'] += $v['data']['fukuanjine']; break;
          default:
            # code...
            break;
        }
      }
      $arr['h24b9vxbed'] = $arr['fvf2axxeoo'] - $arr['gwftl5bgwl'];
      $jzr = strtotime($jizhunri);
      $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}' AND `data`->>'$.htcwgr8bmy' <= '{$jzr}' ORDER BY `data`->>'$.htcwgr8bmy' DESC")[0];
      $company_caiwu1['data'] = _decode($company_caiwu1['data']);
      $arr['hsveec2613'] = $company_caiwu1['data']['hsveec2613'];
      $arr['hsvefurzkn'] = $company_caiwu1['data']['hsvefurzkn'];
      $arr['fsz6tqz4gz'] = $company_caiwu1['data']['fsz6tqz4gz']; 
      $arr['gwfvy19k7r'] = $company_caiwu1['data']['gwfvy19k7r'];
      $arr['gwfvy0y65t'] = $company_caiwu1['data']['gwfvy0y65t']; 
      $arr['huig3o97c3'] = round($company_caiwu1['data']['gwfvy19k7r'] / $company_caiwu1['data']['gwfvy0y65t'],4) * 100;  // task 2024-14497

      // 判断估值方法
      switch ($value['data']['h1o0nyoav0']) {
        case '成本法-投资成本':
          // 本次公允价值 gfpgl2jx71 = 项目池中的累计投资金额 fvf2axxeoo 
          $arr['gfpgl2jx71'] = $arr['fvf2axxeoo'];
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允d值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '成本法-净资产':
          // 项目池中，穿透到项目的二级菜单财务数据模块company_caiwu1，取最新一条财务数据的 归属于母公司股东权益 gwfvy0y65t 的值即可。
          // $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}' order by `data`->>'$.gu25l8ddzs' desc")[0];
          $arr['gfpgl2jx71'] = $arr['gwfvy0y65t'] * $value['data']['h38yx4pf9a'] / 100;
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOCm
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],4) * 100; // 本次估值波动率
          break;
        case '市场法-近期交易法(融资）':
          // 项目池中的当前持股比例 h38yx4pf9a * 融资后企业整体估值 gwfvkm7ev0
          $qyrzqk = $this->em->get("AND `type` = 'qyrzqk' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}' AND `data`->>'$.fund' = '{$value['data']['fund']}' AND `data`->>'$.fi7j65guxn' <= '{$jizhunri}'  order by `data`->>'$.fi7j65guxn' desc")[0];
          $qyrzqk['data'] = _decode($qyrzqk['data']);
          $arr['h38yx4pf9a'] = $qyrzqk['data']['gwfve1ejq8'];
          $arr['fi7j65guz5'] = $qyrzqk['data']['fi7j65guz5'];
          $arr['h0dulxngeo'] = $qyrzqk['data']['h0dulxngeo'];
          $arr['gwfvkm7ev0'] = $qyrzqk['data']['gwfvkm7ev0'];
          $arr['fi7j65guxn'] = $qyrzqk['data']['fi7j65guxn'];
          $arr['gfpgl2jx71'] = ($arr['h38yx4pf9a'] / 100) *  $qyrzqk['data']['gwfvkm7ev0'];
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '收益法-现金流折现法':
          break;
        case '市值法':
          // 本次公允价值=估值基准日那天的收盘价格*当前持股数量
          // 估值基准日那天的收盘价格：收盘价格需要根据已投项目的股票代码gtcl9c7k5s字段去抓取外部股价数据，如果估值基准日那天是非交易日，则向前取数，比如估值基准日是2024年9月30日，这天是周日，没有收盘价，就取前一天的数据为收盘价格，如果前一天也没有收盘价格，就继续往前一天取。
          // 当前持股数量gi1xt7zuig：已投项目池概况字段
          $code = $value['data']['gtcl9c7k5s'];
          // 判断距离 基准日最近的一个工作日
          $spjg = 0;
          for ($i=0; $i < 15; $i++) { 
            $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
            $a = load('c/api')->flow_getStockMarketDataByDate($code,str_replace('/','-',$date));
            if(!empty($a['data']['data']) ){
              $spjg = $a['data']['data']['close'];
              break;
            }
          }
          $arr['spjg'] = $spjg;
          $arr['gfpgl2jx71'] = $spjg * $value['data']['gi1xt7zuig'];
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '临近IPO公司股票市值法':
          // 本次公允价值=当前持股数量*股票发行价*（1-缺乏流动性折扣经验值）
          $arr['gfpgl2jx71'] = $value['data']['gi1xt7zuig'] * $value['data']['hss57vl9wh'] * (1 - $value['data']['hsn9lxfzrl'] / 100);
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '市场法-市净率':
          // 本次公允价值=企业归母净资产*可比公司的平均PB值*当前持股比例*流动性折扣率 
          // 企业归母净资产：根据项目穿透到财务报表模块 company_caiwu1 ，取财务报表中的归属于母公司股东权益 gwfvy0y65t 字段的数据
          // 可比上市公司的平均PB值：根据项目的万得三级行业 sanjhy ，去上市公司项目库 sgsjcsjk 中，查询出同三级行业的项目，把这些项目的PB值全部获取到，然后计算平均值，就是可比上市公司的平均PB值了
          // 流动性折扣率：已投项目池概况中的字段 hsn9lxfzrl
          $htcwgr8bmy = strtotime($jizhunri);
          
          $sid = WEBSID;
          // $sql = "select * from `fengchao` where 1 and `sanjhy` = '{$value['data']['sanjhy']}' limit 0, 10000";
          $sql = "select * from `entity` where 1 and del = 0 and `type` = 'kbssgs_cd' and sid = '{$sid}' and `data`->>'$.sanjhy' = '{$value['data']['sanjhy']}' and `data`->>'$.gfpgkl8loq' = '{$jizhunri}' limit 0, 10000";
          $sgsjcsjk = $this->em->db->query($sql);
          $gtcl9c7k5s_pj = 0;
          $shijinglv = 0;
          $shijinglv_num = 0;
          foreach ($sgsjcsjk as $k => $v) {
            $v['data'] = _decode($v['data']);
            // if($v['shijinglv'] != 0 && $v['shijinglv'] != ''){
            $shijinglv_num++;
            $shijinglv += $v['data']['shijinglv'];
            // }
          }
          if($shijinglv_num){
            $gtcl9c7k5s_pj = round($shijinglv / $shijinglv_num,4);
          }else{
            $gtcl9c7k5s_pj = 0;
          }
          $arr['hsn9gxm3kv'] = $gtcl9c7k5s_pj;
          $arr['hss4x82exz'] = '三级行业';
          $arr['hss4x82exz_label'] = '三级行业';
          $arr['hss2gu7dv6'] = '平均值';
          $arr['hss2gu7dv6_label'] = '平均值';
          $arr['gfpgl2jx71'] = $company_caiwu1['data']['gwfvy0y65t'] * $gtcl9c7k5s_pj * ($value['data']['hsn9lxfzrl'] / 100) * ($value['data']['h38yx4pf9a'] / 100);
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break; 
        case '市场法-市盈率':
          // 本次公允价值=企业归母净利润*可比公司的平均PE值*当前持股比例*流动性折扣率
          // 企业归母净资产：根据项目穿透到财务报表模块 company_caiwu1 ，取财务报表中的归属于母公司股东权益 gwfvy0y65t 字段的数据
          // 可比上市公司的平均PB值：根据项目的万得三级行业 sanjhy ，去上市公司项目库 sgsjcsjk 中，查询出同三级行业的项目，把这些项目的PB值全部获取到，然后计算平均值，就是可比上市公司的平均PB值了
          // 流动性折扣率：已投项目池概况中的字段hsn9lxfzrl
          $htcwgr8bmy = strtotime($jizhunri);
          // $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}' AND `data`->>'$.htcwgr8bmy' <= '{$htcwgr8bmy}' ORDER BY `data`->>'$.htcwgr8bmy' DESC")[0];
          // $company_caiwu1['data'] = _decode($company_caiwu1['data']);
          $sid = WEBSID;
          // $sql = "select * from `fengchao` where 1 and `sanjhy` = '{$value['data']['sanjhy']}' limit 0, 10000";
          $sql = "select * from `entity` where 1 and del = 0 and `type` = 'kbssgs_cd' and sid = '{$sid}' and `data`->>'$.sanjhy' = '{$value['data']['sanjhy']}' and `data`->>'$.gfpgkl8loq' = '{$jizhunri}' limit 0, 10000";
          $sgsjcsjk = $this->em->db->query($sql);
          $gtcl9c7k5s_pj = 0;
          $shiyinglv = 0;
          $shiyinglv_num = 0;
          foreach ($sgsjcsjk as $k => $v) {
            $v['data'] = _decode($v['data']);
            // if($v['shiyinglv'] != 0 && $v['shiyinglv'] != ''){
              $shiyinglv_num++;
              $shiyinglv += $v['data']['shiyinglv'];
            // }
          }
          if($shiyinglv_num){
            $arr['hsn9hcw256'] = $gtcl9c7k5s_pj = round($shiyinglv / $shiyinglv_num,4);
          }else{
            $arr['hsn9hcw256'] = $gtcl9c7k5s_pj = 0;
          }
          $arr['hss4x82exz'] = '三级行业';
          $arr['hss4x82exz_label'] = '三级行业';
          $arr['hss2gu7dv6'] = '平均值';
          $arr['hss2gu7dv6_label'] = '平均值';
          $arr['hsn9hcw256'] = $gtcl9c7k5s_pj;
          $arr['gfpgl2jx71'] = $company_caiwu1['data']['gwfvy19k7r'] * $gtcl9c7k5s_pj * ($value['data']['hsn9lxfzrl'] / 100) * ($value['data']['h38yx4pf9a'] / 100);
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;  
        case '市场法-市销率':
          // 本次公允价值=企业归母净利润*可比公司的平均PE值*当前持股比例*流动性折扣率
          // 企业归母净资产：根据项目穿透到财务报表模块 company_caiwu1 ，取财务报表中的归属于母公司股东权益 gwfvy0y65t 字段的数据
          // 可比上市公司的平均PB值：根据项目的万得三级行业 sanjhy ，去上市公司项目库 sgsjcsjk 中，查询出同三级行业的项目，把这些项目的PB值全部获取到，然后计算平均值，就是可比上市公司的平均PB值了
          // 流动性折扣率：已投项目池概况中的字段hsn9lxfzrl
          $htcwgr8bmy = strtotime($jizhunri);
          $sid = WEBSID;
          // $sql = "select * from `fengchao` where 1 and `sanjhy` = '{$value['data']['sanjhy']}' limit 0, 10000";
          $sql = "select * from `entity` where 1 and del = 0 and `type` = 'kbssgs_cd' and sid = '{$sid}' and `data`->>'$.sanjhy' = '{$value['data']['sanjhy']}' and `data`->>'$.gfpgkl8loq' = '{$jizhunri}' limit 0, 10000";
          $sgsjcsjk = $this->em->db->query($sql);
          $gtcl9c7k5s_pj = 0;
          $shixiaolv = 0;
          $shixiaolv_num = 0;
          foreach ($sgsjcsjk as $k => $v) {
            // if($v['shixiaolv'] != 0 && $v['shixiaolv'] != ''){
              $v['data'] = _decode($v['data']);
              $shixiaolv_num++;
              $shixiaolv += $v['data']['shixiaolv'];
            // }
          }
          if($shixiaolv_num){
            $gtcl9c7k5s_pj = round($shixiaolv / $shixiaolv_num,4);
          }else{
            $gtcl9c7k5s_pj = 0;
          }
          $arr['hsn9hfpyq4'] = $gtcl9c7k5s_pj;
          $arr['hss4x82exz'] = '三级行业';
          $arr['hss4x82exz_label'] = '三级行业';
          $arr['hss2gu7dv6'] = '平均值';
          $arr['hss2gu7dv6_label'] = '平均值';
          $arr['gfpgl2jx71'] = $company_caiwu1['data']['fsz6tqz4gz'] * $gtcl9c7k5s_pj * ($value['data']['hsn9lxfzrl'] / 100) *  ($value['data']['h38yx4pf9a'] / 100);
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '市场价格调整法(AAP)':
          // T：剩余限售期（年）=限售期天数/365  限售期天数=IPO后预计解禁日期 hst8clt4sf-估值基准日（假设共10天）
          $xsq = round((strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
          $xsqts = (strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
          if($value['data']['htrz8pbfjz'] == '个股股价'){
            $arr['get_gegu'] = round($api->get_gegu($xsqts,$jizhunri,$value['data']['gtcl9c7k5s'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
            $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
            $arr['three'] = $three = round($this->normSdist($two/2),4);
            $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
            // $five = round(exp(-$value['date']['']),2)
            $arr['five'] = $five = round(1 * exp((($value['data']['htt3bbe5ht']/100) - ($value['data']['htt3bbe5ht']/100) * 2) * $xsq) * ($three - $four),4);
            // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
            $arr['spjg'] = $bdgj = 0;
            $code = $value['data']['gtcl9c7k5s'];
            $date = $jizhunri;
            for ($i=0; $i < 15; $i++) { 
              $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
              $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
              if(!empty($a['data']['data']) ){
                $arr['spjg'] = $bdgj = $a['data']['data']['close'];
                break;
              }
            }
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            $arr['gfpgl2jx71'] = $bdgj * ( 1 - $five) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['five'] * 100; // 流动性折扣率
          }elseif($value['data']['htrz8pbfjz'] == '行业股价'){
            $arr['get_gegu'] = round($api->get_hygj($xsqts,$jizhunri,$value['data']['ejhy'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
            $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
            $arr['three'] = $three = round($this->normSdist($two/2),4);
            $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
            // $five = round(exp(-$value['date']['']),2)
            $arr['five'] = $five = round(1 * exp((($value['data']['htt3bbe5ht']/100) - ($value['data']['htt3bbe5ht']/100) * 2) * $xsq) * ($three - $four),4);
            // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            $arr['spjg'] = $bdgj = 0;
            $code = $value['data']['gtcl9c7k5s'];
            $date = $jizhunri;
            for ($i=0; $i < 15; $i++) { 
              $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
              $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
              if(!empty($a['data']['data']) ){
                $arr['spjg'] = $bdgj = $a['data']['data']['close'];
                break;
              }
            }
            $arr['gfpgl2jx71'] = $bdgj * ( 1 - $five) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['five'] * 100; // 流动性折扣率
          }else{
            continue;
          }
          // S：估值基准日那天的行业平均收盘价格=估值基准日对应的交易日所有二级行业项目的收盘价格的平均数（可以直接为1计算）
          $pjsp = 1;
          break;
        case '市场价格调整法(BS)':
          // T：剩余限售期（年）=限售期天数/365  限售期天数=IPO后预计解禁日期 hst8clt4sf-估值基准日（假设共10天）
          $xsq = round((strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
          $xsqts = (strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
          //  获取 rf
          $rf = $this->em->get("AND `type` = 'guzhi_gzsyl' AND `del` = 0 AND `data`->>'$.htjlkwbanp' = '{$jizhunri}'")[0];
          $rf['data'] = _decode($rf['data']);
          $guzhi_gzsyl_n =array(
            '0.00' => 'htjkye8wbn',
            '0.08' => 'htjkyenwk8',
            '0.17' => 'htrycw0bsa',
            '0.25' => 'htjkyf9kwr',
            '0.50' => 'htjkyfocqp',
            '0.75' => 'htjkyg22jh',
            '1.00' => 'htjkyge9l2',
            '2.00' => 'htjkygr5ci',
            '3.00' => 'htjkyh2ygb',
            '5.00' => 'htjkyhfcnm',
            '7.00' => 'htjkyhqml4',
            '10.00' => 'htjl0tpben',
            '15.00' => 'htjl0u6hu7',
            '20.00' => 'htjl0ujxec',
            '30.00' => 'htjl0uxd31', 
            '40.00' => 'htjl0v9gdh',
            '50.00' => 'htjl0vmgci'
          );
          $guzhi_gzsyl_num = 0;
          if(in_array($xsq,array_keys($guzhi_gzsyl_n))){
						$guzhi_gzsyl_num = $guzhi_gzsyl_n[$xsq] /100;
					}else{
						$min = 0;
						$max = 0;
						foreach (array_keys($guzhi_gzsyl_n) as $q => $w) {
							if($w < $xsq){ 
								$min = $w;
							}else{
								$max = $w;
								break;
							}
							
						}
						$min_val = $rf['data'][$guzhi_gzsyl_n[$min]]; 
						$max_val = $rf['data'][$guzhi_gzsyl_n[$max]];
 						$guzhi_gzsyl_num = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($xsq - $min) + $min_val,4) / 100;
					}
          if($value['data']['htrz8pbfjz'] == '个股股价'){
            $arr['get_gegu'] = round($api->get_gegu($xsqts,$jizhunri,$value['data']['gtcl9c7k5s'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            // ①=EX*EXP( - rf *t )
            $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
            // ②=σ*t^0.5
            $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
            // (LN(P/EX)+(rf+σ*σ/2)*t)/②
            $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
            // ④=③-②
            $arr['four'] = $four = round($three - $two,4);
            // ⑤=NORMDIST(④,0,1,TRUE)*①
            $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
            // ⑥=NORMDIST(③,0,1,TRUE)*P   
            $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
            // ⑦=⑤-⑥
            $arr['seven'] = $seven = round($five - $six,4);
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            $arr['spjg'] = $bdgj = 0;
            $code = $value['data']['gtcl9c7k5s'];
            $date = $jizhunri;
            for ($i=0; $i < 15; $i++) { 
              $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
              $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
              if(!empty($a['data']['data']) ){
                $arr['spjg'] = $bdgj = $a['data']['data']['close'];
                break;
              }
            }
            $arr['gfpgl2jx71'] = $bdgj * ( 1 - $seven) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['seven'] * 100; // 流动性折扣率
          }elseif($value['data']['htrz8pbfjz'] == '行业股价'){
            $arr['get_gegu'] = round($api->get_hygj($xsqts,$jizhunri,$value['data']['ejhy'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            // ①=EX*EXP( - rf *t )
            $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
            // ②=σ*t^0.5
            $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
            // (LN(P/EX)+(rf+σ*σ/2)*t)/②
            $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
            // ④=③-②
            $arr['four'] = $four = round($three - $two,4);
            // ⑤=NORMDIST(④,0,1,TRUE)*①
            $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
            // ⑥=NORMDIST(③,0,1,TRUE)*P   
            $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
            // ⑦=⑤-⑥
            $arr['seven'] = $seven = round($five - $six,4);
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            $arr['spjg'] = $bdgj = 0;
            $code = $value['data']['gtcl9c7k5s'];
            $date = $jizhunri;
            for ($i=0; $i < 15; $i++) { 
              $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
              $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
              if(!empty($a['data']['data']) ){
                $arr['spjg'] = $bdgj = $a['data']['data']['close'];
                break;
              }
            }
            $arr['gfpgl2jx71'] = $bdgj * ( 1 - $seven) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['seven'] * 100; // 流动性折扣率
          }else{
            continue;
          }
          break;
        default:
          # code...
          break;
      }
      $data['data'] = $arr;
      $data['type'] = 'xiangmuguzhi';
      $res = $this->em->replace($data,"xiangmuguzhi","add",false,false,false,false);
      switch ($value['data']['h1o0nyoav0']) {
        case '市场法-市净率':
        case '市场法-市盈率':
        case '市场法-市销率':
          // 获取对应三行业所有数据
          $jzr_s = $jizhunri;
          // $sql = "select * from `fengchao` where 1 and `sanjhy` = '{$value['data']['sanjhy']}' and `gfpgkl8loq` = '{$jzr_s}' limit 0, 10000";
          $sql = "select * from `entity` where 1 and `del` = 0 and `type` = 'kbssgs_cd' and `data`->>'$.ejhy' = '{$value['data']['ejhy']}' and `data`->>'$.gfpgkl8loq' = '{$jzr_s}' and `sid` = '{$sid}' limit 0, 10000";
          $sgsjcsjk = $this->em->db->query($sql);
					$sxl_arr = array();
					$syl_arr = array();
					$sjl_arr = array();
					$ttm_arr = array();
					$s_num = 0;
          $sgsjcsjk_ejhy = array();
          $sgsjcsjk_sanjhy = array();
          foreach ($sgsjcsjk as $k => $v) {
            $v['data'] = _decode($v['data']);
            $kbssgs = array();
            $kbssgs['sid'] = WEBSID;
            $kbssgs['data'] = $v['data'];
            $kbssgs['data']['_rel'] = $res;
            if($v['data']['sanjhy'] == $value['data']['sanjhy']){
              $s_num++;
              if($v['data']['shixiaolv'] >= 0 && is_numeric($v['data']['shixiaolv']))$sxl_arr[] = $v['data']['shixiaolv'];
              if($v['data']['shiyinglv'] >= 0 && is_numeric($v['data']['shiyinglv']))$syl_arr[] = $v['data']['shiyinglv'];
              if($v['data']['shijinglv'] >= 0 && is_numeric($v['data']['shijinglv']))$sjl_arr[] = $v['data']['shijinglv'];
              if($v['data']['roettm'] >= 0 && is_numeric($v['data']['roettm']))$ttm_arr[] = $v['data']['roettm'];
              $kbssgs['type'] = 'kbssgs3';
              $sgsjcsjk_sanjhy[] = $kbssgs;
              $kbssgs['type'] = 'kbssgs';
              $sgsjcsjk_ejhy[] = $kbssgs;
              // $this->em->replace($kbssgs,"kbssgs","add",false,false,false,false);
            }else{
              $kbssgs['type'] = 'kbssgs';
              $sgsjcsjk_ejhy[] = $kbssgs;
            }
            
          }
          $this->em->bunch_add($sgsjcsjk_sanjhy);
          $this->em->bunch_add($sgsjcsjk_ejhy);
					$ckcs['type'] = 'guzhi_ckcs';
          // 最大值
          sort($sxl_arr);
          $ckcs['data']['htiqnfqzdj'] = '市场法-市销率';
          $ckcs['data']['htiqnfqzdj_label'] = '市场法-市销率';
          $last = count($sxl_arr);
          $ckcs['data']['htiqlsmdbc'] = round($sxl_arr[$last-1],4); // 最大值
          // 第三四分位
          $fen = 3 * $last / 4;
          if(floor($fen) == $fen){
            $san_si = round(($sxl_arr[$fen-1] + $sxl_arr[$fen]) / 2,4);
          }else{ 
            $san_si = $sxl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
          $ckcs['data']['htiqltiooq'] = round($san_si,4); // 第三四分位取均值
          $all = 0;
          foreach ($sxl_arr as $k => $v) {
            $all += $v; 
          }
          $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
          $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
          $zhongjian = $last / 2;
          if(floor($zhongjian) == $zhongjian){
            $ckcs['data']['htiqmgzkt7'] = round(($sxl_arr[$zhongjian-0.5] + $sxl_arr[$zhongjian]) / 2,4);
          }else{
            $kss = (int)($zhongjian-0.5);
            $ckcs['data']['htiqmgzkt7'] = round($sxl_arr[$kss],4);
          }
          $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
          // 第一四分位
          $fen = $last / 4;
          if(floor($fen) == $fen){
            $yi_si = round(($sxl_arr[$fen-1] + $sxl_arr[$fen]) / 2,4);
          }else{
            $yi_si = $sxl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
          $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
          $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
          $ckcs['data']['htiqncdra2'] = round($sxl_arr[0],4); // 最小值
          $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
          $ckcs['data']['htrq306jgi'] = $value['data']['sanjhy'];
          $ckcs['data']['_rel'] = $res;
          $sxl_data = $ckcs['data'];
					$this->em->replace($ckcs,"guzhi_ckcs","add",false,false,false,false);

          // 最大值
          sort($syl_arr);
          $ckcs['data']['htiqnfqzdj'] = '市场法-市盈率';
          $ckcs['data']['htiqnfqzdj_label'] = '市场法-市盈率';
          $last = count($syl_arr);
          $ckcs['data']['htiqlsmdbc'] = round($syl_arr[$last-1],4);  // 最大值
          

          // 第三四分位
          $fen = 3 * $last / 4;
          if(floor($fen) == $fen){
            $san_si = round(($syl_arr[$fen-1] + $syl_arr[$fen]) / 2,4);
          }else{
            $san_si = $syl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
          $ckcs['data']['htiqltiooq'] = round($san_si,4);  // 第三四分位取均值
          $all = 0;
          foreach ($syl_arr as $k => $v) {
            $all += $v;
          }
          $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
          $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
          $zhongjian = $last / 2;
          if(floor($zhongjian) == $zhongjian){
            $ckcs['data']['htiqmgzkt7'] = round(($syl_arr[$zhongjian-0.5] + $syl_arr[$zhongjian]) / 2,4);
          }else{
            $kss = (int)($zhongjian-0.5);
            $ckcs['data']['htiqmgzkt7'] = round($syl_arr[$kss],4); 
          }
          $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
          // 第一四分位
          $fen = $last / 4;
          if(floor($fen) == $fen){
            $yi_si = round(($syl_arr[$fen-1] + $syl_arr[$fen]) / 2,4);
          }else{
            $yi_si = $syl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
          $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
          $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
          $ckcs['data']['htiqncdra2'] = round($syl_arr[0],4); // 最小值
          $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
          $ckcs['data']['htrq306jgi'] = $value['data']['sanjhy'];
          $ckcs['data']['_rel'] = $res;
          $syl_data = $ckcs['data'];
					$this->em->replace($ckcs,"guzhi_ckcs","add",false,false,false,false);

          // 最大值
          sort($sjl_arr);
          $ckcs['data']['htiqnfqzdj'] = '市场法-市净率';
          $ckcs['data']['htiqnfqzdj_label'] = '市场法-市净率';
          $last = count($sjl_arr);
          $ckcs['data']['htiqlsmdbc'] = round($sjl_arr[$last-1],4);  // 最大值
          // 第三四分位
          $fen = 3 * $last / 4;
          if(floor($fen) == $fen){
            $san_si = round(($sjl_arr[$fen-1] + $sjl_arr[$fen]) / 2,4);
          }else{
            $san_si = ($sjl_arr[ceil($fen)-1]);
          }
          $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
          $ckcs['data']['htiqltiooq'] = round($san_si,4); // 第三四分位取均值
          $all = 0;
          foreach ($sjl_arr as $k => $v) {
            $all += $v;
          }
          $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
          $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
          $zhongjian = $last / 2;
          if(floor($zhongjian) == $zhongjian){
            $ckcs['data']['htiqmgzkt7'] = round(($sjl_arr[$zhongjian-0.5] + $sjl_arr[$zhongjian]) / 2,4);
          }else{
            $kss = (int)($zhongjian-0.5);
            $ckcs['data']['htiqmgzkt7'] = round($sjl_arr[$kss],4); 
          }
          $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
          // 第一四分位
          $fen = $last / 4;
          if(floor($fen) == $fen){
            $yi_si = round(($sjl_arr[$fen-1] + $sjl_arr[$fen]) / 2,4);
          }else{
            $yi_si = $sjl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
          $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
          $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
          $ckcs['data']['htiqncdra2'] = round($sjl_arr[0],4);  // 最小值
          $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
          $ckcs['data']['htrq306jgi'] = $value['data']['sanjhy'];
          $ckcs['data']['_rel'] = $res;
          $sjl_data = $ckcs['data'];
					$this->em->replace($ckcs,"guzhi_ckcs","add",false,false,false,false);

          // 最大值
          sort($ttm_arr);
          $ckcs['data']['htiqnfqzdj'] = '净资产收益率';
          $ckcs['data']['htiqnfqzdj_label'] = '净资产收益率';
          $last = count($ttm_arr);
          $ckcs['data']['htiqlsmdbc'] = round($ttm_arr[$last-1],4); // 最大值
          // 第三四分位
          $fen = 3 * $last / 4;
          if(floor($fen) == $fen){
            $san_si = round(($ttm_arr[$fen-1] + $ttm_arr[$fen]) / 2,4);
          }else{ 
            $san_si = $ttm_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
          $ckcs['data']['htiqltiooq'] = round($san_si,4); // 第三四分位取均值
          $all = 0;
          foreach ($ttm_arr as $k => $v) {
            $all += $v; 
          }
          $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
          $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
          $zhongjian = $last / 2;
          if(floor($zhongjian) == $zhongjian){
            $ckcs['data']['htiqmgzkt7'] = round(($ttm_arr[$zhongjian-0.5] + $ttm_arr[$zhongjian]) / 2,4);
          }else{
            $kss = (int)($zhongjian-0.5);
            $ckcs['data']['htiqmgzkt7'] = round($ttm_arr[$kss],4);
          }
          $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
          // 第一四分位
          $fen = $last / 4;
          if(floor($fen) == $fen){
            $yi_si = round(($ttm_arr[$fen-1] + $ttm_arr[$fen]) / 2,4);
          }else{
            $yi_si = $ttm_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
          $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
          $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
          $ckcs['data']['htiqncdra2'] = round($ttm_arr[0],4); // 最小值
          $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
          $ckcs['data']['htrq306jgi'] = $value['data']['sanjhy'];
          $ckcs['data']['_rel'] = $res;
					$this->em->replace($ckcs,"guzhi_ckcs","add",false,false,false,false);

          $bidui_data = array();
          if($value['data']['h1o0nyoav0'] == '市场法-市净率'){ $bidui_data = $sjl_data;}
          if($value['data']['h1o0nyoav0'] == '市场法-市盈率'){ $bidui_data = $syl_data;}
          if($value['data']['h1o0nyoav0'] == '市场法-市销率'){ $bidui_data = $sxl_data;}
          $bidui_zhi = '';
          $bidui_key = '';
          foreach ($ckcs['data'] as $k => $v) {
            if(is_numeric($v)){
              if($bidui_zhi == ''){
                $bidui_zhi = abs($arr['huig3o97c3'] - $v);
                $bidui_key = $k;
              }else{
                if(abs($arr['huig3o97c3'] - $v) < $bidui_zhi){
                  $bidui_zhi = abs($arr['huig3o97c3'] - $v);
                  $bidui_key = $k;
                }
              }
            }
          }
          $hss2gu7dv6_key = array(
            'htiqlsmdbc' => '最大值',
            'htiqlt34py' => '最大值和第三四分位取均值',
            'htiqltiooq' => '第三四分位',
            'htiqltvtqr' => '平均值和第三四分位取均值',
            'htiqluaru0' => '中位数和第三四分位取均值',
            'htiqlun6qb' => '平均值',
            'htiqmgzkt7' => '中位数',
            'htiqmhnn7l' => '第一四分位和平均值取均值',
            'htiqmi8ve8' => '第一四分位和中位数取均值',
            'htiqn5h4zx' => '第一四分位',
            'htiqn8gitk' => '最小值和第一四分位取均值',
            'htiqncdra2' => '最小值'
          );
          $gtcl9c7k5s_pj = $bidui_data[$bidui_key];
          $info = $this->em->get_one($res);
          $info['data'] = _decode($info['data']);
          $info['data']['hss2gu7dv6'] = $hss2gu7dv6_key[$bidui_key];
          $info['data']['hss2gu7dv6_label'] = $hss2gu7dv6_key[$bidui_key];
          if($value['data']['h1o0nyoav0'] == '市场法-市净率'){ 
            $info['data']['hsn9gxm3kv'] = $gtcl9c7k5s_pj;
            $info['data']['gfpgl2jx71'] = $info['data']['gwfvy0y65t'] * $gtcl9c7k5s_pj * (1-$info['data']['hsn9lxfzrl'] / 100) *  ($info['data']['h38yx4pf9a'] / 100);
          }
          if($value['data']['h1o0nyoav0'] == '市场法-市盈率'){ 
            $info['data']['hsn9hcw256'] = $gtcl9c7k5s_pj;
            $info['data']['gfpgl2jx71'] = $info['data']['gwfvy19k7r'] * $gtcl9c7k5s_pj * (1-$info['data']['hsn9lxfzrl'] / 100) *  ($info['data']['h38yx4pf9a'] / 100);
          }
          if($value['data']['h1o0nyoav0'] == '市场法-市销率'){ 
            $info['data']['hsn9hfpyq4'] = $gtcl9c7k5s_pj;
            $info['data']['gfpgl2jx71'] = $info['data']['fsz6tqz4gz'] * $gtcl9c7k5s_pj * (1-$info['data']['hsn9lxfzrl'] / 100) *  ($info['data']['h38yx4pf9a'] / 100);
          }
          $info['data']['h8950owxca'] = round($info['gfpgl2jx71'] / $info['data']['h24b9vxbed'],4); // 未退出部分MOC
          $info['data']['h89510l4a9'] = $info['data']['gfpgl2jx71'] - $info['data']['bcgzqgyjz']; // 本次公允价值波动 
          $info['data']['h8950tefzc'] = round($info['data']['h89510l4a9'] / $info['data']['bcgzqgyjz'],6) * 100; // 本次估值波动率
          $this->em->replace($info,"xiangmuguzhi","update",false,false,false,false);
          // $arr['huig3o97c3']
          break;
        
        default:
          # code...
          break; 
      }
      if($value['data']['h1o0nyoav0'] == '收益法-现金流折现法'){
				$data = $this->em->get_one($res);
				$data['data'] = _decode($data['data']);
				$data['data']['gfpgl2jx71'] = 0;
				$gzfftzmx_gk = $this->em->get("AND `type` = 'gzfftzmx_gk' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}'");
				$guzhi_qyzsyl_n =array(
					'0.00' => 'htjkye8wbn',
					'0.08' => 'htjkyenwk8',
					'0.25' => 'htjkyf9kwr',
					'0.50' => 'htjkyfocqp',
					'0.75' => 'htjkyg22jh',
					'1.00' => 'htjkyge9l2',
					'2.00' => 'htjkygr5ci',
					'3.00' => 'htjkyh2ygb',
					'4.00' => 'htjkyhfcnm',
					'5.00' => 'htjkyhqml4',
					'6.00' => 'htjl0tpben',
					'7.00' => 'htjl0u6hu7',
					'8.00' => 'htjl0ujxec',
					'9.00' => 'htjl0uxd31',
					'10.00' => 'htjl0v9gdh',
					'15.00' => 'htjl0vmgci',
					'20.00' => 'htjl0w0c2w',
					'30.00' => 'htjl0wcc59'
				);
        $gzfftzmx_arr = array();
        $sid = WEBSID;
        foreach ($gzfftzmx_gk as $k => $v) {
					$v['data'] = _decode($v['data']);
					$gzfftzmx['type'] = 'gzfftzmx';
					$gzfftzmx['data'] = $v['data'];
					$gzfftzmx['data']['h2v8g9k39a'] = $jizhunri;
					$gzfftzmx['data']['htir2jb73f'] = 100;
					// 计息天数(年） h2v8gb12z6 ：（ 回购计息截止日 h8ev03erwb — 回购起息日 h8ev02mw41）/365
					$gzfftzmx['data']['h2v8gb12z6'] = round((strtotime($v['data']['h8ev03erwb']) - strtotime($v['data']['h8ev02mw41'])) / 86400 / 365 ,4); 
					// 应付利息（复利） hthj6sqxa4= 计息本金 *（1+回购利率  ）的n次方-计息本金ggpec8xi57
					$gzfftzmx['data']['hthj6sqxa4'] = round($v['data']['ggpec8xi57'] * pow(1 + ($v['data']['h2cmti4smx'] / 100),$gzfftzmx['data']['h2v8gb12z6']) - $v['data']['ggpec8xi57'],4);
					// 应付利息(单利） hthj6iehyr=计息本金*计息天数(年）*回购利率
					$gzfftzmx['data']['hthj6iehyr'] = $v['data']['ggpec8xi57'] * $gzfftzmx['data']['h2v8gb12z6'] * ($v['data']['h2cmti4smx'] / 100);
					// 预计回款本金折现年限=(预计回款日期-估值基准日）/365
					$gzfftzmx['data']['hthjosfmh1'] = round((strtotime($v['data']['htcshzviba']) - strtotime($jizhunri)) / 86400 / 365,4);
					// 预计回款利息折现年限=(预计利息回款日期-估值基准日）/365
					$gzfftzmx['data']['hthjoypl7f'] = round((strtotime($v['data']['htcsi59133']) - strtotime($jizhunri)) / 86400 / 365,4);
		
					$guzhi_qyzsyl = $this->em->get("AND `type` = 'guzhi_qyzsyl' AND `del` = 0 AND `data`->>'$.hthml1py5n' = '{$v['data']['hthml1py5n']}' AND `data`->>'$.htjlkwbanp' = '{$jizhunri}'")[0];
					$guzhi_qyzsyl['data'] = _decode($guzhi_qyzsyl['data']);
					// 本金折现率 h2v8h490xl
					$benjin_nianhua = round((strtotime($v['data']['htcshzviba']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
					
					$gzfftzmx['data']['h2v8h490xl'] = 0;
					if(in_array($benjin_nianhua,array_keys($guzhi_qyzsyl_n))){
						$zxl = $guzhi_qyzsyl_n[$benjin_nianhua];
					}else{
						$min = 0;
						$max = 0;
						foreach (array_keys($guzhi_qyzsyl_n) as $q => $w) {
							if($w < $benjin_nianhua){
								$min = $w;
							}else{
								$max = $w;
								break;
							}
							
						}
						$min_val = $guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$min]]; 
						$max_val = $guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$max]];
 						$gzfftzmx['data']['h2v8h490xl'] = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($benjin_nianhua - $min) + $min_val,4);
					}
					// 本金公允价值=计息本金/(1+本金折现率）的预计回款本金折现年限次方*综合折扣率
					$gzfftzmx['data']['hthjp807fg'] = round($v['data']['ggpec8xi57'] / pow((1 + $gzfftzmx['data']['h2v8h490xl'] / 100),$benjin_nianhua),4);
					// 利息折现率 hthiq35o6a	
					$lixi_nianhua = round((strtotime($v['data']['htcsi59133']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
					$gzfftzmx['data']['hthiq35o6a'] = 0;
					if(in_array($lixi_nianhua,array_keys($guzhi_qyzsyl_n))){
						$zxl = $guzhi_qyzsyl_n[$lixi_nianhua];
					}else{
						$min = 0;
						$max = 0;
						foreach (array_keys($guzhi_qyzsyl_n) as $q => $w) {
							if($w < $lixi_nianhua){
								$min = $w;
							}else{
								$max = $w;
								break;
							}
							
						}
						$min_val =$guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$min]];         
						$max_val =$guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$max]];
						$gzfftzmx['data']['hthiq35o6a'] = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($lixi_nianhua - $min) + $min_val,4);
						
					}
					if($v['data']['h2v8g8wqg7'] == '单利'){  
						$gzfftzmx['data']['hthjqfasmc'] = round($gzfftzmx['data']['hthj6iehyr'] / pow((1 + $gzfftzmx['data']['hthiq35o6a'] / 100),$lixi_nianhua),4);
					}
					if($v['data']['h2v8g8wqg7'] == '复利'){
						$gzfftzmx['data']['hthjqfasmc'] = round($gzfftzmx['data']['hthj6sqxa4'] / pow((1 + $gzfftzmx['data']['hthiq35o6a'] / 100),$lixi_nianhua),4);
            
					}
          if(is_infinite($gzfftzmx['data']['hthjqfasmc']) )$gzfftzmx['data']['hthjqfasmc']=0;
          if(is_infinite($gzfftzmx['data']['hthjp807fg']) )$gzfftzmx['data']['hthjp807fg']=0;
					$gzfftzmx['data']['h2v8hab2h5'] = $gzfftzmx['data']['hthjqfasmc'] + $gzfftzmx['data']['hthjp807fg'];
          if($v['data']['htcs9dpff1'] == '本金利息未收回'){
						$data['data']['gfpgl2jx71'] += $gzfftzmx['data']['h2v8hab2h5']; 
					}
          if($v['data']['htcs9dpff1'] == '本金已收回(利息未收回)'){
						$data['data']['gfpgl2jx71'] += $gzfftzmx['data']['h2v8hab2h5']; 
					}
          if($v['data']['htcs9dpff1'] == '利息已收回(本金未收回)'){
						$data['data']['gfpgl2jx71'] += $gzfftzmx['data']['h2v8hab2h5']; 
					}
					$gzfftzmx['data']['_rel'] = $res;
					$gzfftzmx['sid'] = $sid;
          $gzfftzmx_arr[] = $gzfftzmx;
					// $this->em->replace($gzfftzmx,"gzfftzmx","add",false,false,false,false);
				}
        $this->em->bunch_add($gzfftzmx_arr); 
				$data['data']['h8950owxca'] = round($data['data']['gfpgl2jx71'] / $data['data']['h24b9vxbed'],4); // 未 退出部分MOC
				$data['data']['h89510l4a9'] = $data['data']['gfpgl2jx71'] - $data['data']['bcgzqgyjz']; // 本次公允d值波动 
				$data['data']['h8950tefzc'] = round($data['data']['h89510l4a9'] / $data['data']['bcgzqgyjz'],6) * 100; // 本次估值波动率
				$data['data']['uuid'] = $res;
				$res = $this->em->replace($data,"xiangmuguzhi","update",false,false,false,false);
      }
    }
    
  }
  function erf_s($x) {  
    // 这是一个简单的近似，实际应用中可能需要更复杂的近似或查找表  
    // 或者使用专门的数学库如math.js  
    $a1 = 0.254829592;  
    $a2 = -0.284496736;  
    $a3 = 1.421413741;  
    $a4 = -1.453152027;  
    $a5 = 1.061405429;  
    $p = 0.3275911;  
  
    // 保存x的符号  
    $sign = 1;  
    if ($x < 0) $sign = -1;  
    $x = abs($x);  
    // A&S formula 7.1.26  
    $t = 1.0 / (1.0 + $p * $x);  
    $y = 1.0 - ((((($a5 * $t + $a4) * $t) + $a3) * $t + $a2) * $t + $a1) * $t * exp(-$x * $x); 
    return $sign * $y;   
  }  
  function normSdist($z){
    return 0.5 * (1 + $this->erf_s($z / sqrt(2)));  
  }
  function get_company_caiwu1()
  {
    $name = $_POST['name'];
    $jizhunri = $_POST['jizhunri'];
    // $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$name}' order by `data`->>'$.gu25l8ddzs' desc")[0];
    $jzr = strtotime($jizhunri);
    $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$name}' AND `data`->>'$.htcwgr8bmy' <= '{$jzr}' ORDER BY `data`->>'$.htcwgr8bmy' DESC")[0];
    $gfpgl2jx71 = _decode($company_caiwu1['data'])['gwfvy0y65t'];
    ajax_return($gfpgl2jx71);

  }
  function szf(){
    $code = $_POST['code'];
    $jizhunri = $_POST['jizhunri'];
    $company = $_POST['company'];
    // 判断距离 基准日最近的一个工作日
		$arr['spjg'] = 0;
    $spjg = 0;
    for ($i=0; $i < 15; $i++) { 
      $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
      $a = load('c/api')->flow_getStockMarketDataByDate($code,str_replace('/','-',$date));
      if(!empty($a['data']['data']) ){
        $spjg = $a['data']['data']['close'];
        break;
      }
    }
		$jzr = strtotime($jizhunri);
		$company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$company}' AND `data`->>'$.htcwgr8bmy' <= '{$jzr}' ORDER BY `data`->>'$.htcwgr8bmy' DESC")[0];
		$company_caiwu1['data'] = _decode($company_caiwu1['data']);
		$arr['hsveec2613'] = $company_caiwu1['data']['hsveec2613'];
		$arr['hsvefurzkn'] = $company_caiwu1['data']['hsvefurzkn'];
		$arr['fsz6tqz4gz'] = $company_caiwu1['data']['fsz6tqz4gz']; 
		$arr['gwfvy19k7r'] = $company_caiwu1['data']['gwfvy19k7r'];
		$arr['gwfvy0y65t'] = $company_caiwu1['data']['gwfvy0y65t'];
		$arr['spjg'] = $spjg;
    ajax_return($arr);
  }
  function scf_sjl(){
    $sanjhy = $_POST['sanjhy'];
    $ejhy = $_POST['ejhy'];
    $jizhunri = $_POST['jizhunri'];
    $name = $_POST['company'];
    $hss2gu7dv6 = $_POST['hss2gu7dv6'];
    $h1o0nyoav0 = $_POST['h1o0nyoav0'];
    $uuid = $_POST['uuid'];
    $flushed = $_POST['flushed'];
    $hss4x82exz = $_POST['hss4x82exz'];
    $htcwgr8bmy = strtotime($jizhunri);
    $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$name}' AND `data`->>'$.htcwgr8bmy' <= '{$htcwgr8bmy}' ORDER BY `data`->>'$.htcwgr8bmy' DESC")[0];
    $company_caiwu1['data'] = _decode($company_caiwu1['data']);
    $sid = WEBSID;
    $arr['gtcl9c7k5s_pj'] = $this->get_ckcs($uuid,$h1o0nyoav0,$hss2gu7dv6,$jizhunri,$sanjhy,$ejhy,$flushed,$hss4x82exz);
    $arr['gtcl9c7k5s_pj'] = $arr['gtcl9c7k5s_pj'] ? $arr['gtcl9c7k5s_pj'] : 0;
    $arr['gwfvy0y65t'] = $company_caiwu1['data']['gwfvy0y65t'];
    ajax_return($arr);

  }
  function scf_syl(){
    $sanjhy = $_POST['sanjhy'];
    $ejhy = $_POST['ejhy'];
    $jizhunri = $_POST['jizhunri'];
    $name = $_POST['company'];
    $hss2gu7dv6 = $_POST['hss2gu7dv6'];
    $h1o0nyoav0 = $_POST['h1o0nyoav0'];
    $hss4x82exz = $_POST['hss4x82exz'];
    $uuid = $_POST['uuid'];
    $flushed = $_POST['flushed'];
    $htcwgr8bmy = strtotime($jizhunri);
    $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$name}' AND `data`->>'$.htcwgr8bmy' <= '{$htcwgr8bmy}' ORDER BY `data`->>'$.htcwgr8bmy' DESC")[0];
    $company_caiwu1['data'] = _decode($company_caiwu1['data']);
    $arr['gtcl9c7k5s_pj'] = $this->get_ckcs($uuid,$h1o0nyoav0,$hss2gu7dv6,$jizhunri,$sanjhy,$ejhy,$flushed,$hss4x82exz);
    $arr['gtcl9c7k5s_pj'] = $arr['gtcl9c7k5s_pj'] ? $arr['gtcl9c7k5s_pj'] : 0;
    $arr['gwfvy19k7r'] = $company_caiwu1['data']['gwfvy19k7r'];
    ajax_return($arr);

  }
  function scf_sxl(){
    $sanjhy = $_POST['sanjhy'];
    $ejhy = $_POST['ejhy'];
    $jizhunri = $_POST['jizhunri'];
    $name = $_POST['company'];
    $hss2gu7dv6 = $_POST['hss2gu7dv6'];
    $h1o0nyoav0 = $_POST['h1o0nyoav0'];
    $hss4x82exz = $_POST['hss4x82exz'];
    $uuid = $_POST['uuid'];
    $flushed = $_POST['flushed'];
    $htcwgr8bmy = strtotime($jizhunri);
    $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$name}' AND `data`->>'$.htcwgr8bmy' <= '{$htcwgr8bmy}' ORDER BY `data`->>'$.htcwgr8bmy' DESC")[0];
    $company_caiwu1['data'] = _decode($company_caiwu1['data']);
    $arr['gtcl9c7k5s_pj'] = $this->get_ckcs($uuid,$h1o0nyoav0,$hss2gu7dv6,$jizhunri,$sanjhy,$ejhy,$flushed,$hss4x82exz);
    $arr['gtcl9c7k5s_pj'] = $arr['gtcl9c7k5s_pj'] ? $arr['gtcl9c7k5s_pj'] : 0;
    $arr['gwfvy0y65t'] = $company_caiwu1['data']['gwfvy0y65t'];
    $arr['fsz6tqz4gz'] = $company_caiwu1['data']['fsz6tqz4gz'];
    ajax_return($arr);
  } 
  function scf_bs(){
    $api = load('c/api');
    $jizhunri = $_POST['jizhunri'];
    $uuid = $_POST['uuid'];
    $company = $_POST['company'];
    $htif8z5z8y = $_POST['htif8z5z8y'];
    $htrz8pbfjz = $_POST['htrz8pbfjz'];  
    $ejhy = $_POST['ejhy'];
    $hthml1py5n = $_POST['hthml1py5n'];
    $gi1xt7zuig = $_POST['gi1xt7zuig'];

    $info = $this->em->get_one($uuid);
    $info['data'] = _decode($info['data']);
    $xsq = round((strtotime($info['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
    $xsqts = (strtotime($info['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
    //  获取 rf
    $rf = $this->em->get("AND `type` = 'guzhi_gzsyl' AND `del` = 0 AND `data`->>'$.htjlkwbanp' = '{$jizhunri}'")[0];
    $rf['data'] = _decode($rf['data']);
    $guzhi_gzsyl_n =array(
      '0.00' => 'htjkye8wbn',
      '0.08' => 'htjkyenwk8',
      '0.17' => 'htrycw0bsa',
      '0.25' => 'htjkyf9kwr',
      '0.50' => 'htjkyfocqp',
      '0.75' => 'htjkyg22jh',
      '1.00' => 'htjkyge9l2',
      '2.00' => 'htjkygr5ci',
      '3.00' => 'htjkyh2ygb',
      '5.00' => 'htjkyhfcnm',
      '7.00' => 'htjkyhqml4',
      '10.00' => 'htjl0tpben',
      '15.00' => 'htjl0u6hu7',
      '20.00' => 'htjl0ujxec',
      '30.00' => 'htjl0uxd31', 
      '40.00' => 'htjl0v9gdh',
      '50.00' => 'htjl0vmgci'
    );
    $guzhi_gzsyl_num = 0;
    if(in_array($xsq,array_keys($guzhi_gzsyl_n))){
      $guzhi_gzsyl_num = $guzhi_gzsyl_n[$xsq];
    }else{
      $min = 0;
      $max = 0;
      foreach (array_keys($guzhi_gzsyl_n) as $q => $w) {
        if($w < $xsq){
          $min = $w;
        }else{
          $max = $w;
          break;
        }
        
      }
      $min_val = $rf['data'][$guzhi_gzsyl_n[$min]]; 
      $max_val = $rf['data'][$guzhi_gzsyl_n[$max]];
      $guzhi_gzsyl_num = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($xsq - $min) + $min_val,4) /100;
    }
    if($htrz8pbfjz== '个股股价'){
      $arr['get_gegu'] = round($api->get_gegu($xsqts,$jizhunri,$info['data']['gtcl9c7k5s'],$info['data']['hst8clt4sf']),4);
      $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
      // ①=EX*EXP( - rf *t )
      $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
      // ②=σ*t^0.5
      $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
      // (LN(P/EX)+(rf+σ*σ/2)*t)/②
      $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
      // ④=③-②
      $arr['four'] = $four = round($three - $two,4);
      // ⑤=NORMDIST(④,0,1,TRUE)*①
      $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
      // ⑥=NORMDIST(③,0,1,TRUE)*P   
      $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
      // ⑦=⑤-⑥
      $arr['seven'] = $seven = round($five - $six,4);
      //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      $arr['spjg'] = $bdgj = 0;
      $code = $info['data']['gtcl9c7k5s'];
      $date = $jizhunri;
      for ($i=0; $i < 15; $i++) { 
        $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
        $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
        if(!empty($a['data']['data']) ){
          $arr['spjg'] = $bdgj = $a['data']['data']['close'];
          break;
        }
      }
      $arr['gfpgl2jx71'] = $bdgj * ( 1 - $seven) * $gi1xt7zuig;
      $arr['hsn9lxfzrl'] = $arr['seven']; // 流动性折扣率
    }elseif($htrz8pbfjz== '行业股价'){
      $arr['get_gegu'] = round($api->get_hygj($xsqts,$jizhunri,$ejhy,$info['data']['hst8clt4sf']),4);
      $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
      // ①=EX*EXP( - rf *t )
      $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
      // ②=σ*t^0.5
      $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
      // (LN(P/EX)+(rf+σ*σ/2)*t)/②
      $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
      // ④=③-②
      $arr['four'] = $four = round($three - $two,4);
      // ⑤=NORMDIST(④,0,1,TRUE)*①
      $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
      // ⑥=NORMDIST(③,0,1,TRUE)*P   
      $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
      // ⑦=⑤-⑥
      $arr['seven'] = $seven = round($five - $six,4);
      //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      $arr['spjg'] = $bdgj = 0;
      $code = $info['data']['gtcl9c7k5s'];
      $date = $jizhunri;
      for ($i=0; $i < 15; $i++) { 
        $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
        $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
        if(!empty($a['data']['data']) ){
          $arr['spjg'] = $bdgj = $a['data']['data']['close'];
          break;
        }
      }
      $arr['gfpgl2jx71'] = $bdgj * ( 1 - $seven) * $gi1xt7zuig;
      $arr['hsn9lxfzrl'] = $arr['seven']; // 流动性折扣率
    }else{
      $arr['gfpgl2jx71'] = 0;
      // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      $arr['spjg'] = $bdgj = 0;
      $code = $info['data']['gtcl9c7k5s'];
      $date = $jizhunri;
      for ($i=0; $i < 15; $i++) { 
        $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
        $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
        if(!empty($a['data']['data']) ){
          $arr['spjg'] = $bdgj = $a['data']['data']['close'];
          break;
        }
      }
      $arr['hsn9lxfzrl'] = 0;
    }
    ajax_return($arr);
  }
  function scf_aap(){
    $api = load('c/api');
    $jizhunri = $_POST['jizhunri'];
    $uuid = $_POST['uuid'];
    $company = $_POST['company'];
    $htif8z5z8y = $_POST['htif8z5z8y'];
    $htrz8pbfjz = $_POST['htrz8pbfjz'];
    $ejhy = $_POST['ejhy'];
    $hthml1py5n = $_POST['hthml1py5n'];
    $htt3bbe5ht = $_POST['htt3bbe5ht'];
    $gi1xt7zuig = $_POST['gi1xt7zuig'];

    $info = $this->em->get_one($uuid);
    $info['data'] = _decode($info['data']);

    $xsq = round((strtotime($info['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
    $xsqts = (strtotime($info['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
    if($htrz8pbfjz == '个股股价'){
      $arr['get_gegu'] = $api->get_gegu($xsqts,$jizhunri,$info['data']['gtcl9c7k5s'],$info['data']['hst8clt4sf']);
      $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
      $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
      $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
      $arr['three'] = $three = round($this->normSdist($two/2),4);
      $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
      // $five = round(exp(-$value['date']['']),2)
      $arr['five'] = $five = round(1 * exp((($htt3bbe5ht/100) - ($htt3bbe5ht/100) * 2) * $xsq) * ($three - $four),4);
      // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
      // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      $arr['spjg'] = $bdgj = 0;
      $code = $info['data']['gtcl9c7k5s'];
      $date = $jizhunri;
      for ($i=0; $i < 15; $i++) { 
        $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
        $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
        if(!empty($a['data']['data']) ){
          $arr['spjg'] = $bdgj = $a['data']['data']['close'];
          break;
        }
      }
      $arr['gfpgl2jx71'] = $bdgj * ( 1 - $five) * $gi1xt7zuig;
      $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
      $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
      $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
      $arr['hsn9lxfzrl'] = $arr['five']; // 流动性折扣率
    }elseif($htrz8pbfjz == '行业股价'){
      $arr['get_gegu'] = $api->get_hygj($xsqts,$jizhunri,$ejhy,$info['data']['hst8clt4sf']);
      $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
      $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
      $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
      $arr['three'] = $three = round($this->normSdist($two/2),4);
      $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
      // $five = round(exp(-$value['date']['']),2)
      $arr['five'] = $five = round(1 * exp((($htt3bbe5ht/100) - ($htt3bbe5ht/100) * 2) * $xsq) * ($three - $four),4);
      // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
      //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      $arr['spjg'] = $bdgj = 0;
      $code = $info['data']['gtcl9c7k5s'];
      $date = $jizhunri;
      for ($i=0; $i < 15; $i++) { 
        $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
        $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
        if(!empty($a['data']['data']) ){
          $arr['spjg'] = $bdgj = $a['data']['data']['close'];
          break;
        }
      }
      $arr['gfpgl2jx71'] = $bdgj * ( 1 - $five) * $gi1xt7zuig;
      $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
      $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
      $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
      $arr['hsn9lxfzrl'] = $arr['five']; // 流动性折扣率
    }else{
      $arr['gfpgl2jx71'] = 0;
      //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      $arr['spjg'] = $bdgj = 0;
      $code = $info['data']['gtcl9c7k5s'];
      $date = $jizhunri;
      for ($i=0; $i < 15; $i++) { 
        $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
        $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
        if(!empty($a['data']['data']) ){
          $arr['spjg'] = $bdgj = $a['data']['data']['close'];
          break;
        }
      }
      $arr['hsn9lxfzrl'] = 0;
    }
    ajax_return($arr);
  }
  function ipo_bs(){
    $api = load('c/api');
    $jizhunri = $_POST['jizhunri'];
    $uuid = $_POST['uuid'];
    $company = $_POST['company'];
    $htif8z5z8y = $_POST['htif8z5z8y'];
    $htrz8pbfjz = $_POST['htrz8pbfjz'];  
    $ejhy = $_POST['ejhy'];
    $hthml1py5n = $_POST['hthml1py5n'];
    $gi1xt7zuig = $_POST['gi1xt7zuig'];
    $hss57vl9wh = $_POST['hss57vl9wh'];

    $info = $this->em->get_one($uuid);
    $info['data'] = _decode($info['data']);
    $xsq = round((strtotime($info['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
    $xsqts = (strtotime($info['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
    //  获取 rf
    $rf = $this->em->get("AND `type` = 'guzhi_gzsyl' AND `del` = 0 AND `data`->>'$.htjlkwbanp' = '{$jizhunri}'")[0];
    $rf['data'] = _decode($rf['data']);
    $guzhi_gzsyl_n =array(
      '0.00' => 'htjkye8wbn',
      '0.08' => 'htjkyenwk8',
      '0.17' => 'htrycw0bsa',
      '0.25' => 'htjkyf9kwr',
      '0.50' => 'htjkyfocqp',
      '0.75' => 'htjkyg22jh',
      '1.00' => 'htjkyge9l2',
      '2.00' => 'htjkygr5ci',
      '3.00' => 'htjkyh2ygb',
      '5.00' => 'htjkyhfcnm',
      '7.00' => 'htjkyhqml4',
      '10.00' => 'htjl0tpben',
      '15.00' => 'htjl0u6hu7',
      '20.00' => 'htjl0ujxec',
      '30.00' => 'htjl0uxd31', 
      '40.00' => 'htjl0v9gdh',
      '50.00' => 'htjl0vmgci'
    );
    $guzhi_gzsyl_num = 0;
    if(in_array($xsq,array_keys($guzhi_gzsyl_n))){
      $guzhi_gzsyl_num = $guzhi_gzsyl_n[$xsq] / 100;
    }else{
      $min = 0;
      $max = 0;
      foreach (array_keys($guzhi_gzsyl_n) as $q => $w) {
        if($w < $xsq){
          $min = $w;
        }else{
          $max = $w;
          break;
        }
        
      }
      $min_val = $rf['data'][$guzhi_gzsyl_n[$min]]; 
      $max_val = $rf['data'][$guzhi_gzsyl_n[$max]];
      $guzhi_gzsyl_num = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($xsq - $min) + $min_val,4) / 100;
    }
    if($htrz8pbfjz== '个股股价'){
      $arr['get_gegu'] = round($api->get_gegu($xsqts,$jizhunri,$info['data']['gtcl9c7k5s'],$info['data']['hst8clt4sf']),4);
      $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
      // ①=EX*EXP( - rf *t )
      $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
      // ②=σ*t^0.5
      $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
      // (LN(P/EX)+(rf+σ*σ/2)*t)/②
      $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
      // ④=③-②
      $arr['four'] = $four = round($three - $two,4);
      // ⑤=NORMDIST(④,0,1,TRUE)*①
      $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
      // ⑥=NORMDIST(③,0,1,TRUE)*P   
      $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
      // ⑦=⑤-⑥
      $arr['seven'] = $seven = round($five - $six,4);
      //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      // $arr['spjg'] = $bdgj = 0;
      // $code = $info['data']['gtcl9c7k5s'];
      // $date = $jizhunri;
      // for ($i=0; $i < 15; $i++) { 
      //   $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
      //   $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
      //   if(!empty($a['data']['data']) ){
      //     $arr['spjg'] = $bdgj = $a['data']['data']['close'];
      //     break;
      //   }
      // }
      $arr['gfpgl2jx71'] = $hss57vl9wh * ( 1 - $seven) * $gi1xt7zuig;
      $arr['hsn9lxfzrl'] = $arr['seven']; // 流动性折扣率
    }elseif($htrz8pbfjz== '行业股价'){
      $arr['get_gegu'] = round($api->get_hygj($xsqts,$jizhunri,$ejhy,$info['data']['hst8clt4sf']),4);
      $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
      // ①=EX*EXP( - rf *t )
      $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
      // ②=σ*t^0.5
      $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
      // (LN(P/EX)+(rf+σ*σ/2)*t)/②
      $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
      // ④=③-②
      $arr['four'] = $four = round($three - $two,4);
      // ⑤=NORMDIST(④,0,1,TRUE)*①
      $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
      // ⑥=NORMDIST(③,0,1,TRUE)*P   
      $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
      // ⑦=⑤-⑥
      $arr['seven'] = $seven = round($five - $six,4);
      //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      // $arr['spjg'] = $bdgj = 0;
      // $code = $info['data']['gtcl9c7k5s'];
      // $date = $jizhunri;
      // for ($i=0; $i < 15; $i++) { 
      //   $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
      //   $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
      //   if(!empty($a['data']['data']) ){
      //     $arr['spjg'] = $bdgj = $a['data']['data']['close'];
      //     break;
      //   }
      // }
      $arr['gfpgl2jx71'] = $hss57vl9wh * ( 1 - $seven) * $gi1xt7zuig;
      $arr['hsn9lxfzrl'] = $arr['seven']; // 流动性折扣率
    }else{
      $arr['gfpgl2jx71'] = 0;
      // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      $arr['spjg'] = $bdgj = 0;
      $code = $info['data']['gtcl9c7k5s'];
      $date = $jizhunri;
      for ($i=0; $i < 15; $i++) { 
        $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
        $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
        if(!empty($a['data']['data']) ){
          $arr['spjg'] = $bdgj = $a['data']['data']['close'];
          break;
        }
      }
      $arr['hsn9lxfzrl'] = 0;
    }
    ajax_return($arr);
  }
  function ipo_aap(){
    $api = load('c/api');
    $jizhunri = $_POST['jizhunri'];
    $uuid = $_POST['uuid'];
    $company = $_POST['company'];
    $htif8z5z8y = $_POST['htif8z5z8y'];
    $htrz8pbfjz = $_POST['htrz8pbfjz'];
    $ejhy = $_POST['ejhy'];
    $hthml1py5n = $_POST['hthml1py5n'];
    $htt3bbe5ht = $_POST['htt3bbe5ht'];
    $gi1xt7zuig = $_POST['gi1xt7zuig'];
    $hss57vl9wh = $_POST['hss57vl9wh'];
    
    $info = $this->em->get_one($uuid);
    $info['data'] = _decode($info['data']);

    $xsq = round((strtotime($info['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
    $xsqts = (strtotime($info['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
    if($htrz8pbfjz == '个股股价'){
      $arr['get_gegu'] = $api->get_gegu($xsqts,$jizhunri,$info['data']['gtcl9c7k5s'],$info['data']['hst8clt4sf']);
      $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
      $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
      $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
      $arr['three'] = $three = round($this->normSdist($two/2),4);
      $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
      // $five = round(exp(-$value['date']['']),2)
      $arr['five'] = $five = round(1 * exp((($htt3bbe5ht/100) - ($htt3bbe5ht/100) * 2) * $xsq) * ($three - $four),4);
      // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
      // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      // $arr['spjg'] = $bdgj = 0;
      // $code = $info['data']['gtcl9c7k5s'];
      // $date = $jizhunri;
      // for ($i=0; $i < 15; $i++) { 
      //   $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
      //   $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
      //   if(!empty($a['data']['data']) ){
      //     $arr['spjg'] = $bdgj = $a['data']['data']['close'];
      //     break;
      //   }
      // }
      $arr['gfpgl2jx71'] = $hss57vl9wh * ( 1 - $five) * $gi1xt7zuig;
      $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
      $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
      $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
      $arr['hsn9lxfzrl'] = $arr['five']; // 流动性折扣率
    }elseif($htrz8pbfjz == '行业股价'){
      $arr['get_gegu'] = $api->get_hygj($xsqts,$jizhunri,$ejhy,$info['data']['hst8clt4sf']);
      $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
      $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
      $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
      $arr['three'] = $three = round($this->normSdist($two/2),4);
      $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
      // $five = round(exp(-$value['date']['']),2)
      $arr['five'] = $five = round(1 * exp((($htt3bbe5ht/100) - ($htt3bbe5ht/100) * 2) * $xsq) * ($three - $four),4);
      // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
      //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      // $arr['spjg'] = $bdgj = 0;
      // $code = $info['data']['gtcl9c7k5s'];
      // $date = $jizhunri;
      // for ($i=0; $i < 15; $i++) { 
      //   $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
      //   $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
      //   if(!empty($a['data']['data']) ){
      //     $arr['spjg'] = $bdgj = $a['data']['data']['close'];
      //     break;
      //   }
      // }
      $arr['gfpgl2jx71'] = $hss57vl9wh * ( 1 - $five) * $gi1xt7zuig;
      $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
      $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
      $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
      $arr['hsn9lxfzrl'] = $arr['five']; // 流动性折扣率
    }else{
      $arr['gfpgl2jx71'] = 0;
      //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($info['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
      $arr['spjg'] = $bdgj = 0;
      $code = $info['data']['gtcl9c7k5s'];
      $date = $jizhunri;
      for ($i=0; $i < 15; $i++) { 
        $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
        $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
        if(!empty($a['data']['data']) ){
          $arr['spjg'] = $bdgj = $a['data']['data']['close'];
          break;
        }
      }
      $arr['hsn9lxfzrl'] = 0;
    }
    ajax_return($arr);
  }
  function kbssggs(){
    $uuid = $_POST['uuid'];
    $hss4x82exz = $_POST['hss4x82exz'];
    $ejhy = $_POST['ejhy'];
    $sanjhy = $_POST['sanjhy'];
    $jizhunri = $_POST['jizhunri'];
		$sid = WEBSID;
    $sql = "UPDATE `entity` SET `del` = '1' WHERE `type` = 'kbssgs' AND `_rel` = '{$uuid}' AND `sid` = '{$sid}'";
    $this->em->db->query($sql);
    $sql = '';
    if($ejhy){
      $sql = "select * from `entity` where 1 and `type` = 'kbssgs_cd' and `data`->>'$.ejhy' = '{$ejhy}' and `sid` = '{$sid}' limit 0, 10000";
    }
    if($sanjhy){
      $sql = "select * from `entity` where 1 and `type` = 'kbssgs_cd' and `data`->>'$.sanjhy' = '{$sanjhy}' and `sid` = '{$sid}'  limit 0, 10000";
    } 
    if($ejhy == '' && $sanjhy == ''){
      return;
    }
    // 获取对应三行业所有数据
    $sgsjcsjk = $this->em->db->query($sql);
    foreach ($sgsjcsjk as $k => $v) {
      $v['data'] = _decode($v['data']);
      $kbssgs = array();
      $kbssgs['type'] = 'kbssgs';
      $kbssgs['data'] = $v['data'];
      $kbssgs['data']['_rel'] = $uuid;
      $this->em->replace($kbssgs,"kbssgs","add",false,false,false,false);
    }
		$arr['ss'] = 1;
		ajax_return($arr);
  }
  function get_xjlzxf(){
    $uuid = $_GET['uuid'];
    $htif8z5z8y = $_GET['htif8z5z8y'];
    $gzfftzmx_gk = $this->em->get("AND `type` = 'gzfftzmx' AND `del` = 0 AND `_rel` = '{$uuid}'");
    $data['gfpgl2jx71'] = 0;
    foreach ($gzfftzmx_gk as $k => $v) {
			$v['data'] = _decode($v['data']);
      if($v['data']['htcs9dpff1'] == '本金利息未收回'){
        $data['gfpgl2jx71'] += $v['data']['h2v8hab2h5']; 
      }
      if($v['data']['htcs9dpff1'] == '本金已收回(利息未收回)'){
        $data['gfpgl2jx71'] += $v['data']['h2v8hab2h5']; 
      }
      if($v['data']['htcs9dpff1'] == '利息已收回(本金未收回)'){
        $data['gfpgl2jx71'] += $v['data']['h2v8hab2h5']; 
      }
		}
		$data['gfpgl2jx71'] = $data['gfpgl2jx71'] * $htif8z5z8y / 100;
		ajax_return($data);
  }
	function syf_xjlzxf(){
		$data['gfpgl2jx71'] = 0;
		$uuid = $_POST['uuid'];
		$company = $_POST['company'];
		$jizhunri = $_POST['jizhunri'];
		$htif8z5z8y = $_POST['htif8z5z8y'];
		$refresh = $_POST['refresh'];
    if($refresh == 'true'){
      $gzfftzmx_gk = $this->em->get("AND `type` = 'gzfftzmx_gk' AND `del` = 0 AND `data`->>'$.name' = '{$company}'");
      $sid = WEBSID;
      $sql = "UPDATE `entity` SET `del` = '1' WHERE `type` = 'gzfftzmx' AND `_rel` = '{$uuid}' AND `sid` = '{$sid}'";
      $this->em->db->query($sql);
      $guzhi_qyzsyl_n =array(
        '0.00' => 'htjkye8wbn',
        '0.08' => 'htjkyenwk8',
        '0.25' => 'htjkyf9kwr',
        '0.50' => 'htjkyfocqp',
        '0.75' => 'htjkyg22jh',
        '1.00' => 'htjkyge9l2',
        '2.00' => 'htjkygr5ci',
        '3.00' => 'htjkyh2ygb',
        '4.00' => 'htjkyhfcnm',
        '5.00' => 'htjkyhqml4',
        '6.00' => 'htjl0tpben',
        '7.00' => 'htjl0u6hu7',
        '8.00' => 'htjl0ujxec',
        '9.00' => 'htjl0uxd31',
        '10.00' => 'htjl0v9gdh',
        '15.00' => 'htjl0vmgci',
        '20.00' => 'htjl0w0c2w',
        '30.00' => 'htjl0wcc59'
      );
      $gzfftzmx_arr = array();
      foreach ($gzfftzmx_gk as $k => $v) {
        $v['data'] = _decode($v['data']);
        $gzfftzmx['type'] = 'gzfftzmx';
        $gzfftzmx['data'] = $v['data'];
        $gzfftzmx['data']['h2v8g9k39a'] = $jizhunri;
        $gzfftzmx['data']['htir2jb73f'] = 100;
        // 计息天数(年） h2v8gb12z6 ：（ 回购计息截止日 h8ev03erwb — 回购起息日 h8ev02mw41）/365
        $gzfftzmx['data']['h2v8gb12z6'] = round((strtotime($v['data']['h8ev03erwb']) - strtotime($v['data']['h8ev02mw41'])) / 86400 / 365 ,4); 
        // 应付利息（复利） hthj6sqxa4= 计息本金 *（1+回购利率  ）的n次方-计息本金ggpec8xi57
        $gzfftzmx['data']['hthj6sqxa4'] = round($v['data']['ggpec8xi57'] * pow(1 + ($v['data']['h2cmti4smx'] / 100),$gzfftzmx['data']['h2v8gb12z6']) - $v['data']['ggpec8xi57'],4);
        // 应付利息(单利） hthj6iehyr=计息本金*计息天数(年）*回购利率
        $gzfftzmx['data']['hthj6iehyr'] = $v['data']['ggpec8xi57'] * $gzfftzmx['data']['h2v8gb12z6'] * ($v['data']['h2cmti4smx'] / 100);
        // 预计回款本金折现年限=(预计回款日期-估值基准日）/365
        $gzfftzmx['data']['hthjosfmh1'] = round((strtotime($v['data']['htcshzviba']) - strtotime($jizhunri)) / 86400 / 365,4);
        // 预计回款利息折现年限=(预计利息回款日期-估值基准日）/365
        $gzfftzmx['data']['hthjoypl7f'] = round((strtotime($v['data']['htcsi59133']) - strtotime($jizhunri)) / 86400 / 365,4);

        $guzhi_qyzsyl = $this->em->get("AND `type` = 'guzhi_qyzsyl' AND `del` = 0 AND `data`->>'$.hthml1py5n' = '{$v['data']['hthml1py5n']}' AND `data`->>'$.htjlkwbanp' = '{$jizhunri}'")[0];
        $guzhi_qyzsyl['data'] = _decode($guzhi_qyzsyl['data']);
        // 本金折现率 h2v8h490xl
        $benjin_nianhua = round((strtotime($v['data']['htcshzviba']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
        $gzfftzmx['data']['h2v8h490xl'] = 0;
        if(in_array($benjin_nianhua,array_keys($guzhi_qyzsyl_n))){
          $zxl = $guzhi_qyzsyl_n[$benjin_nianhua];
        }else{
          $min = 0;
          $max = 0;
          foreach (array_keys($guzhi_qyzsyl_n) as $q => $w) {
            if($w < $benjin_nianhua){
              $min = $w;
            }else{
              $max = $w;
              break;
            }
            
          }
          $min_val = $guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$min]];
          $max_val = $guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$max]];
          $gzfftzmx['data']['h2v8h490xl'] = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($benjin_nianhua - $min) + $min_val,4);
        }
        // 本金公允价值=计息本金/(1+本金折现率）的预计回款本金折现年限次方*综合折扣率
        $gzfftzmx['data']['hthjp807fg'] = round($v['data']['ggpec8xi57'] / pow((1 + $gzfftzmx['data']['h2v8h490xl'] / 100),$benjin_nianhua),4);
        // 利息折现率 hthiq35o6a	
        $lixi_nianhua = round((strtotime($v['data']['htcsi59133']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
        $gzfftzmx['data']['hthiq35o6a'] = 0;
        if(in_array($lixi_nianhua,array_keys($guzhi_qyzsyl_n))){
          $zxl = $guzhi_qyzsyl_n[$lixi_nianhua];
        }else{
          $min = 0;
          $max = 0;
          foreach (array_keys($guzhi_qyzsyl_n) as $q => $w) {
            if($w < $lixi_nianhua){
              $min = $w;
            }else{
              $max = $w;
              break;
            }
            
          }
          $min_val =$guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$min]];
          $max_val =$guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$max]];
          $gzfftzmx['data']['hthiq35o6a'] = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($lixi_nianhua - $min) + $min_val,4);
        }
        if($v['data']['h2v8g8wqg7'] == '单利'){
          $gzfftzmx['data']['hthjqfasmc'] = round($gzfftzmx['data']['hthj6iehyr'] / pow((1 + $gzfftzmx['data']['hthiq35o6a'] / 100),$lixi_nianhua),4);
        }
        if($v['data']['h2v8g8wqg7'] == '复利'){
          $gzfftzmx['data']['hthjqfasmc'] = round($gzfftzmx['data']['hthj6sqxa4'] / pow((1 + $gzfftzmx['data']['hthiq35o6a'] / 100),$lixi_nianhua),4);
        }
        if(is_infinite($gzfftzmx['data']['hthjp807fg']) )$gzfftzmx['data']['hthjp807fg']=0;
        if(is_infinite($gzfftzmx['data']['hthjqfasmc']) )$gzfftzmx['data']['hthjqfasmc']=0;
        $gzfftzmx['data']['h2v8hab2h5'] = $gzfftzmx['data']['hthjqfasmc'] + $gzfftzmx['data']['hthjp807fg'];
        if($v['data']['htcs9dpff1'] == '本金利息未收回'){
          $data['gfpgl2jx71'] += $gzfftzmx['data']['h2v8hab2h5']; 
        }
        if($v['data']['htcs9dpff1'] == '本金已收回(利息未收回)'){
          $data['gfpgl2jx71'] += $gzfftzmx['data']['h2v8hab2h5']; 
        }
        if($v['data']['htcs9dpff1'] == '利息已收回(本金未收回)'){
          $data['gfpgl2jx71'] += $gzfftzmx['data']['h2v8hab2h5']; 
        }
        $gzfftzmx['data']['_rel'] = $uuid;
        $gzfftzmx['sid'] = WEBSID;
        $gzfftzmx_arr[] = $gzfftzmx;
        // $this->em->replace($gzfftzmx,"gzfftzmx","add",false,false,false,false);
        $data['gfpgl2jx71'] = $data['gfpgl2jx71'] * $htif8z5z8y / 100;
      }
      $this->em->bunch_add($gzfftzmx_arr);
    }else{
      $gzfftzmx = $this->em->get("AND `type` = 'gzfftzmx' AND `del` = 0 AND `_rel` = '{$uuid}'");
      $data['gfpgl2jx71'] = 0;
      foreach ($gzfftzmx as $key => $value) {
        $value['data'] = _decode($value['data']); 
        if($value['data']['htcs9dpff1'] == '本金利息未收回'){
          $data['gfpgl2jx71'] += $value['data']['h2v8hab2h5']; 
        }
        if($value['data']['htcs9dpff1'] == '本金已收回(利息未收回)'){
          $data['gfpgl2jx71'] += $value['data']['h2v8hab2h5']; 
        }
        if($value['data']['htcs9dpff1'] == '利息已收回(本金未收回)'){
          $data['gfpgl2jx71'] += $value['data']['h2v8hab2h5']; 
        }
      }
      $data['gfpgl2jx71'] = $data['gfpgl2jx71'] * $htif8z5z8y / 100;
    }
		
		
		// $data['h8950owxca'] = round($data['gfpgl2jx71'] / $data['h24b9vxbed'],4); // 未 退出部分MOC
		// $data['h89510l4a9'] = $data['gfpgl2jx71'] - $data['bcgzqgyjz']; // 本次公允d值波动 
		// $data['h8950tefzc'] = round($data['h89510l4a9'] / $data['bcgzqgyjz'],6) * 100; // 本次估值波动率
		ajax_return($data);
		// $data['data']['uuid'] = $res;
		// $res = $this->em->replace($data,"xiangmuguzhi","update",false,false,false,false);
	}
	function zxl(){
		$jizhunri = '2024-09-06';
		$gzfftzmx_gk = $this->em->get("AND `type` = 'gzfftzmx_gk' AND `del` = 0 AND `data`->>'$.name' = 'h1dmcfhqsm'");
		$guzhi_qyzsyl_n =array(
			'0.00' => 'htjkye8wbn',
			'0.08' => 'htjkyenwk8',
			'0.25' => 'htjkyf9kwr',
			'0.50' => 'htjkyfocqp',
			'0.75' => 'htjkyg22jh',
			'1.00' => 'htjkyge9l2',
			'2.00' => 'htjkygr5ci',
			'3.00' => 'htjkyh2ygb',
			'4.00' => 'htjkyhfcnm',
			'5.00' => 'htjkyhqml4',
			'6.00' => 'htjl0tpben',
			'7.00' => 'htjl0u6hu7',
			'8.00' => 'htjl0ujxec',
			'9.00' => 'htjl0uxd31',
			'10.00' => 'htjl0v9gdh',
			'15.00' => 'htjl0vmgci',
			'20.00' => 'htjl0w0c2w',
			'30.00' => 'htjl0wcc59'
		);
		foreach ($gzfftzmx_gk as $k => $v) {
			$v['data'] = _decode($v['data']);
			$gzfftzmx['type'] = 'gzfftzmx';
			$gzfftzmx['data'] = $v['data'];
			$gzfftzmx['data']['h2v8g9k39a'] = $jizhunri;
			// 计息天数(年） h2v8gb12z6 ：（ 回购计息截止日 h8ev03erwb — 回购起息日 h8ev02mw41）/365
			$gzfftzmx['data']['h2v8gb12z6'] = round((strtotime($v['data']['h8ev03erwb']) - strtotime($v['data']['h8ev02mw41'])) / 86400 / 365 ,4); 
			// 应付利息（复利） hthj6sqxa4= 计息本金 *（1+回购利率  ）的n次方-计息本金ggpec8xi57
			$gzfftzmx['data']['hthj6sqxa4'] = round($v['data']['ggpec8xi57'] * pow(1 + ($v['data']['h2cmti4smx'] / 100),$gzfftzmx['data']['h2v8gb12z6']) - $v['data']['ggpec8xi57'],4);
			// 应付利息(单利） hthj6iehyr=计息本金*计息天数(年）*回购利率
			$gzfftzmx['data']['hthj6iehyr'] = $v['data']['ggpec8xi57'] * $gzfftzmx['data']['h2v8gb12z6'] * ($v['data']['h2cmti4smx'] / 100);
			// 预计回款本金折现年限=(预计回款日期-估值基准日）/365
			$gzfftzmx['data']['hthjosfmh1'] = round((strtotime($v['data']['htcshzviba']) - strtotime($jizhunri)) / 86400 / 365,4);
			// 预计回款利息折现年限=(预计利息回款日期-估值基准日）/365
			$gzfftzmx['data']['hthjoypl7f'] = round((strtotime($v['data']['htcsi59133']) - strtotime($jizhunri)) / 86400 / 365,4);

			$guzhi_qyzsyl = $this->em->get("AND `type` = 'guzhi_qyzsyl' AND `del` = 0 AND `data`->>'$.hthml1py5n' = '{$v['data']['hthml1py5n']}' AND `data`->>'$.htjlkwbanp' = '{$jizhunri}'")[0];
			$guzhi_qyzsyl['data'] = _decode($guzhi_qyzsyl['data']);
			// 本金折现率 h2v8h490xl
			$benjin_nianhua = round((strtotime($v['data']['htcshzviba']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
			$gzfftzmx['data']['h2v8h490xl'] = 0;
			if(in_array($benjin_nianhua,array_keys($guzhi_qyzsyl_n))){
				$zxl = $guzhi_qyzsyl_n[$benjin_nianhua];
			}else{
				$min = 0;
				$max = 0;
				foreach (array_keys($guzhi_qyzsyl_n) as $q => $w) {
					if($w < $benjin_nianhua){
						$min = $w;
					}else{
						$max = $w;
						break;
					}
					
				}
				$min_val = $guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$min]];
				$max_val = $guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$max]];
				$gzfftzmx['data']['h2v8h490xl'] = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($benjin_nianhua - $min) + $min_val,4);
			}
			// 本金公允价值=计息本金/(1+本金折现率）的预计回款本金折现年限次方*综合折扣率
			$gzfftzmx['data']['hthjp807fg'] = round($v['data']['ggpec8xi57'] / pow((1 + $gzfftzmx['data']['h2v8h490xl'] / 100),$benjin_nianhua),4);
			// 利息折现率 hthiq35o6a	
			$lixi_nianhua = round((strtotime($v['data']['htcsi59133']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
			$gzfftzmx['data']['hthiq35o6a'] = 0;
			if(in_array($lixi_nianhua,array_keys($guzhi_qyzsyl_n))){
				$zxl = $guzhi_qyzsyl_n[$lixi_nianhua];
			}else{
				$min = 0;
				$max = 0;
				foreach (array_keys($guzhi_qyzsyl_n) as $q => $w) {
					if($w < $lixi_nianhua){
						$min = $w;
					}else{
						$max = $w;
						break;
					}
					
				}
				$min_val =$guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$min]];
				$max_val =$guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$max]];
				$gzfftzmx['data']['hthiq35o6a'] = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($lixi_nianhua - $min) + $min_val,4);
			}
			if($v['data']['h2v8g8wqg7'] == '单利'){
				$gzfftzmx['data']['hthjqfasmc'] = round($gzfftzmx['data']['hthj6iehyr'] / pow((1 + $gzfftzmx['data']['hthiq35o6a'] / 100),$lixi_nianhua),4);
			}
			if($v['data']['h2v8g8wqg7'] == '复利'){
				$gzfftzmx['data']['hthjqfasmc'] = round($gzfftzmx['data']['hthj6sqxa4'] / pow((1 + $gzfftzmx['data']['hthiq35o6a'] / 100),$lixi_nianhua),4);
			}
			$gzfftzmx['data']['_rel'] = $res;
			$this->em->replace($gzfftzmx,"gzfftzmx","add",false,false,false,false);
		}
	}
	function ckcs(){
		// $sql = "select * from `fengchao` where 1 and `sanjhy` = '能源设备与服务' limit 0, 10000";
    $sql = "select * from `entity` where 1 and `type` = 'kbssgs_cd' and `data`->>'$.sanjhy' = '化工' limit 0, 10000";
		$sgsjcsjk = $this->em->db->query($sql);
		$sxl_arr = array();
		$syl_arr = array();
		$sjl_arr = array();
		$s_num = 0;
		foreach ($sgsjcsjk as $k => $v) {
      $v['data'] = _decode($v['data']);
			$kbssgs = array();
			$s_num++;
			$sxl_arr[] = $v['data']['shixiaolv'] ? $v['data']['shixiaolv'] : 0;
			$syl_arr[] = $v['data']['shiyinglv'] ? $v['data']['shiyinglv'] : 0;
			$sjl_arr[] = $v['data']['shijinglv'] ? $v['data']['shijinglv'] : 0;
			$kbssgs['type'] = 'kbssgs';
			$kbssgs['data'] = $v;
			$kbssgs['data']['_rel'] = $res;
			// $this->em->replace($kbssgs,"kbssgs","add",false,false,false,false);
		}
		$ckcs['type'] = 'guzhi_ckcs';
		// 最大值
		$ckcs['data']['type'] = '市盈率';
		sort($sjl_arr);
		$last = count($sjl_arr);
		$ckcs['data']['htiqlsmdbc'] = $sjl_arr[$last-1]; // 最大值
		// 第三四分位
		$fen = 3 * $last / 4;
    if(floor($fen) == $fen){
      $san_si = round(($sjl_arr[$fen-1] + $sjl_arr[$fen]) / 2,4);
    }else{
      $san_si = ($sjl_arr[ceil($fen)-1]);
    }
		$ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
		$ckcs['data']['htiqltiooq'] = $san_si; // 第三四分位取均值
		$all = 0;
		foreach ($sjl_arr as $k => $v) {
			$all += $v;
		}
		$ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
		$ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
		$zhongjian = $last / 2;
		if(floor($zhongjian) == $zhongjian){
			$ckcs['data']['htiqmgzkt7'] = round(($sjl_arr[$zhongjian-0.5] + $sjl_arr[$zhongjian]) / 2,4);
		}else{
			$kss = (int)($zhongjian-0.5);
			$ckcs['data']['htiqmgzkt7'] = $sjl_arr[$kss];
		}
		$ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
		// 第一四分位
		$fen = $last / 4;
    if(floor($fen) == $fen){
      $yi_si = round(($sjl_arr[$fen-1] + $sjl_arr[$fen]) / 2,4);
    }else{
      $yi_si = ($sjl_arr[ceil($fen)-1]);
    }
		$ckcs['data']['htiqn5h4zx'] = $yi_si; // 第一四分位
		$ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
		$ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
		$ckcs['data']['htiqncdra2'] = round($sjl_arr[0],4); // 最小值
		$ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
    // return $ckcs;
		dump($ckcs['data']);
	}
  function get_ckcs($uuid,$h1o0nyoav0,$hss2gu7dv6,$jizhunri,$sanjhy,$ejhy,$flushed,$hss4x82exz){
    $sid = WEBSID;
    // 判断是否生成过  参考乘数
    // if($sanjhy != ''){
    //   $is_ckcs = $this->em->get("AND `del` = 0 AND `type` = 'guzhi_ckcs' AND `_rel` = '{$uuid}' AND `data`->>'$.htiqnfqzdj' = '{$h1o0nyoav0}' AND `data`->>'$.htrq306jgi' = '{$sanjhy}' ")[0];
    // }
    // if($ejhy != ''){
    //   $is_ckcs = $this->em->get("AND `del` = 0 AND `type` = 'guzhi_ckcs' AND `_rel` = '{$uuid}' AND `data`->>'$.htiqnfqzdj' = '{$h1o0nyoav0}' AND `data`->>'$.htrq306jgi' = '{$ejhy}' ")[0];
    // }
    // if(!$is_ckcs){
    $sql = "UPDATE `entity` SET `del` = 1 WHERE `type` = 'guzhi_ckcs' AND `_rel` = '{$uuid}' AND `sid` = '{$sid}'";
    $this->em->db->query($sql);
    // if($flushed == 'true'){
      // $sql = "UPDATE `entity` SET `del` = 1 WHERE `type` = 'kbssgs' AND `_rel` = '{$uuid}' AND `sid` = '{$sid}'";
      // $this->em->db->query($sql);
      // 获取对应三行业所有数据
      // $sql = "select * from `fengchao` where 1 and `sanjhy` = '{$value['data']['sanjhy']}' and `gfpgkl8loq` = '{$jzr_s}' limit 0, 10000";
      if($hss4x82exz == '三级行业'){
        $sql = "select * from `entity` where 1 and `type` = 'kbssgs3' and `_rel` = '{$uuid}' and `del` = '0' AND `sid` = '{$sid}' limit 0, 10000";
      }
      if($hss4x82exz == '二级行业'){
        $sql = "select * from `entity` where 1 and `type` = 'kbssgs' and `_rel` = '{$uuid}' and `del` = '0' AND `sid` = '{$sid}' limit 0, 10000";
      }
      // if($sanjhy != ''){
      //   $sql = "select * from `entity` where 1 and `type` = 'kbssgs_cd' and `data`->>'$.sanjhy' = '{$sanjhy}' and `data`->>'$.gfpgkl8loq' = '{$jizhunri}' AND `del` = 0 AND `sid` = '{$sid}' limit 0, 10000";
      // }else{
      //   $sql = "select * from `entity` where 1 and `type` = 'kbssgs_cd' and `data`->>'$.ejhy' = '{$ejhy}' and `data`->>'$.gfpgkl8loq' = '{$jizhunri}' AND `del` = 0 AND `sid` = '{$sid}' limit 0, 10000";
      // }
      $sgsjcsjk = $this->em->db->query($sql);
      $sxl_arr = array();
      $syl_arr = array();
      $sjl_arr = array();
      $ttm_arr = array();
      $s_num = 0;
      $kbssgs_all = array();
      foreach ($sgsjcsjk as $k => $v) {
        $v['data'] = _decode($v['data']);
        $kbssgs = array();
        $s_num++;
        if($v['data']['shixiaolv'] >= 0 && is_numeric($v['data']['shixiaolv']))$sxl_arr[] = $v['data']['shixiaolv'];
        if($v['data']['shiyinglv'] >= 0 && is_numeric($v['data']['shiyinglv']))$syl_arr[] = $v['data']['shiyinglv'];
        if($v['data']['shijinglv'] >= 0 && is_numeric($v['data']['shijinglv']))$sjl_arr[] = $v['data']['shijinglv'];
        if($v['data']['roettm'] >= 0 && is_numeric($v['data']['roettm']))$ttm_arr[] = $v['data']['roettm'];
        // $kbssgs['uuid'] = uuid();
        // $kbssgs['sid'] = $sid;
        // $kbssgs['type'] = 'kbssgs';
        // $kbssgs['data'] = $v['data'];
        // $kbssgs['data']['_rel'] = $uuid;
        // $kbssgs_all[] = $kbssgs;
      }
      // $this->em->bunch_add($kbssgs_all);
    // }else{
    //   $sql = "select * from `entity` where 1 and `type` = 'kbssgs' and `data`->>'$._rel' = '{$uuid}' AND `del` = 0 AND `sid` = '{$sid}' limit 0, 10000";
    //   $sgsjcsjk = $this->em->db->query($sql);
    //   $sxl_arr = array();
    //   $syl_arr = array();
    //   $sjl_arr = array();
    //   $s_num = 0;
    //   foreach ($sgsjcsjk as $k => $v) {
    //     $v['data'] = _decode($v['data']);
    //     $kbssgs = array();
    //     $s_num++;
    //     $sxl_arr[] = $v['data']['shixiaolv'];
    //     $syl_arr[] = $v['data']['shiyinglv'];
    //     $sjl_arr[] = $v['data']['shijinglv'];
    //   }
    // }
    $ckcs['type'] = 'guzhi_ckcs';
    // 最大值
    sort($sxl_arr);
    $ckcs['data']['htiqnfqzdj'] = '市场法-市销率';
    $ckcs['data']['htiqnfqzdj_label'] = '市场法-市销率';
    $last = count($sxl_arr);
    $ckcs['data']['htiqlsmdbc'] = round($sxl_arr[$last-1],4); // 最大值
    // 第三四分位
    $fen = 3 * $last / 4;
    if(floor($fen) == $fen){
      $san_si = round(($sxl_arr[$fen-1] + $sxl_arr[$fen]) / 2,4);
    }else{ 
      $san_si = $sxl_arr[ceil($fen)-1];
    }
    $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
    $ckcs['data']['htiqltiooq'] = round($san_si,4); // 第三四分位取均值
    $all = 0;
    foreach ($sxl_arr as $k => $v) {
      $all += $v; 
    }
    $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
    $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
    $zhongjian = $last / 2;
    if(floor($zhongjian) == $zhongjian){
      $ckcs['data']['htiqmgzkt7'] = round(($sxl_arr[$zhongjian-0.5] + $sxl_arr[$zhongjian]) / 2,4);
    }else{
      $kss = (int)($zhongjian-0.5);
      $ckcs['data']['htiqmgzkt7'] = round($sxl_arr[$kss],4);
    }
    $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
    // 第一四分位
    $fen = $last / 4;
    if(floor($fen) == $fen){
      $yi_si = round(($sxl_arr[$fen-1] + $sxl_arr[$fen]) / 2,4);
    }else{
      $yi_si = $sxl_arr[ceil($fen)-1];
    }
    $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
    $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
    $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
    $ckcs['data']['htiqncdra2'] = round($sxl_arr[0],4); // 最小值
    $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
    if($sanjhy != ''){ $ckcs['data']['htrq306jgi'] = $sanjhy; }
    if($ejhy != ''){ $ckcs['data']['htrq306jgi'] = $ejhy; }
    $ckcs['data']['_rel'] = $uuid;
    $this->em->replace_bare($ckcs);

    // 最大值
    sort($syl_arr);
    $ckcs['data']['htiqnfqzdj'] = '市场法-市盈率';
    $ckcs['data']['htiqnfqzdj_label'] = '市场法-市盈率';
    $last = count($syl_arr);
    $ckcs['data']['htiqlsmdbc'] = round($syl_arr[$last-1],4);  // 最大值
    
    // 第三四分位
    $fen = 3 * $last / 4;
    if(floor($fen) == $fen){
      $san_si = round(($syl_arr[$fen-1] + $syl_arr[$fen]) / 2,4);
    }else{
      $san_si = $syl_arr[ceil($fen)-1];
    }
    $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
    $ckcs['data']['htiqltiooq'] = round($san_si,4);  // 第三四分位取均值
    $all = 0;
    foreach ($syl_arr as $k => $v) {
      $all += $v;
    }
    $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
    $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
    $zhongjian = $last / 2;
    if(floor($zhongjian) == $zhongjian){
      $ckcs['data']['htiqmgzkt7'] = round(($syl_arr[$zhongjian-0.5] + $syl_arr[$zhongjian]) / 2,4);
    }else{
      $kss = (int)($zhongjian-0.5);
      $ckcs['data']['htiqmgzkt7'] = round($syl_arr[$kss],4); 
    }
    $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
    // 第一四分位
    $fen = $last / 4;
    if(floor($fen) == $fen){
      $yi_si = round(($syl_arr[$fen-1] + $syl_arr[$fen]) / 2,4);
    }else{
      $yi_si = $syl_arr[ceil($fen)-1];
    }
    $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
    $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
    $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
    $ckcs['data']['htiqncdra2'] = round($syl_arr[0],4); // 最小值
    $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
    if($sanjhy != ''){ $ckcs['data']['htrq306jgi'] = $sanjhy; }
    if($ejhy != ''){ $ckcs['data']['htrq306jgi'] = $ejhy; }
    $ckcs['data']['_rel'] = $uuid;
    $this->em->replace_bare($ckcs);

    // 最大值
    sort($sjl_arr);
    $ckcs['data']['htiqnfqzdj'] = '市场法-市净率';
    $ckcs['data']['htiqnfqzdj_label'] = '市场法-市净率';
    $last = count($sjl_arr);
    $ckcs['data']['htiqlsmdbc'] = round($sjl_arr[$last-1],4);  // 最大值
    // 第三四分位
    $fen = 3 * $last / 4;
    if(floor($fen) == $fen){
      $san_si = round(($sjl_arr[$fen-1] + $sjl_arr[$fen]) / 2,4);
    }else{
      $san_si = ($sjl_arr[ceil($fen)-1]);
    }
    $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
    $ckcs['data']['htiqltiooq'] = round($san_si,4); // 第三四分位取均值
    $all = 0;
    foreach ($sjl_arr as $k => $v) {
      $all += $v;
    }
    $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
    $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
    $zhongjian = $last / 2;
    if(floor($zhongjian) == $zhongjian){
      $ckcs['data']['htiqmgzkt7'] = round(($sjl_arr[$zhongjian-0.5] + $sjl_arr[$zhongjian]) / 2,4);
    }else{
      $kss = (int)($zhongjian-0.5);
      $ckcs['data']['htiqmgzkt7'] = round($sjl_arr[$kss],4); 
    }
    $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
    // 第一四分位
    $fen = $last / 4;
    if(floor($fen) == $fen){
      $yi_si = round(($sjl_arr[$fen-1] + $sjl_arr[$fen]) / 2,4);
    }else{
      $yi_si = $sjl_arr[ceil($fen)-1];
    }
    $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
    $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
    $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
    $ckcs['data']['htiqncdra2'] = round($sjl_arr[0],4);  // 最小值
    $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
    if($sanjhy != ''){ $ckcs['data']['htrq306jgi'] = $sanjhy; }
    if($ejhy != ''){ $ckcs['data']['htrq306jgi'] = $ejhy; }
    $ckcs['data']['_rel'] = $uuid;
    $this->em->replace_bare($ckcs);

    // 最大值
    sort($ttm_arr);
    $ckcs['data']['htiqnfqzdj'] = '净资产收益率';
    $ckcs['data']['htiqnfqzdj_label'] = '净资产收益率';
    $last = count($ttm_arr);
    $ckcs['data']['htiqlsmdbc'] = round($ttm_arr[$last-1],4);  // 最大值
    
    // 第三四分位
    $fen = 3 * $last / 4;
    if(floor($fen) == $fen){
      $san_si = round(($ttm_arr[$fen-1] + $ttm_arr[$fen]) / 2,4);
    }else{
      $san_si = $ttm_arr[ceil($fen)-1];
    }
    $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
    $ckcs['data']['htiqltiooq'] = round($san_si,4);  // 第三四分位取均值
    $all = 0;
    foreach ($ttm_arr as $k => $v) {
      $all += $v;
    }
    $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
    $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
    $zhongjian = $last / 2;
    if(floor($zhongjian) == $zhongjian){
      $ckcs['data']['htiqmgzkt7'] = round(($ttm_arr[$zhongjian-0.5] + $ttm_arr[$zhongjian]) / 2,4);
    }else{
      $kss = (int)($zhongjian-0.5);
      $ckcs['data']['htiqmgzkt7'] = round($ttm_arr[$kss],4); 
    }
    $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
    // 第一四分位
    $fen = $last / 4;
    if(floor($fen) == $fen){
      $yi_si = round(($ttm_arr[$fen-1] + $ttm_arr[$fen]) / 2,4);
    }else{
      $yi_si = $ttm_arr[ceil($fen)-1];
    }
    $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
    $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
    $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
    $ckcs['data']['htiqncdra2'] = round($ttm_arr[0],4); // 最小值
    $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
    if($sanjhy != ''){ $ckcs['data']['htrq306jgi'] = $sanjhy; }
    if($ejhy != ''){ $ckcs['data']['htrq306jgi'] = $ejhy; }
    $ckcs['data']['_rel'] = $uuid;
    $this->em->replace_bare($ckcs);

    $data = $this->em->get("AND `del` = 0 AND `type` = 'guzhi_ckcs' AND `_rel` = '{$uuid}' AND `data`->>'$.htiqnfqzdj' = '{$h1o0nyoav0}'")[0];
    $data['data'] = _decode($data['data']);
    $num = 0;
    switch ($hss2gu7dv6) {
      case '最大值': $num = $data['data']['htiqlsmdbc'];  break;
      case '最大值和第三四分位取均值': $num = $data['data']['htiqlt34py'];  break;
      case '第三四分位': $num = $data['data']['htiqltiooq'];  break;
      case '平均值和第三四分位取均值': $num = $data['data']['htiqltvtqr'];  break;
      case '中位数和第三四分位取均值': $num = $data['data']['htiqluaru0'];  break;
      case '平均值': $num = $data['data']['htiqlun6qb'];  break;
      case '中位数': $num = $data['data']['htiqmgzkt7'];  break;
      case '第一四分位和平均值取均值': $num = $data['data']['htiqmhnn7l'];  break;
      case '第一四分位和中位数取均值': $num = $data['data']['htiqmi8ve8'];  break;
      case '第一四分位': $num = $data['data']['htiqn5h4zx'];  break;
      case '最小值和第一四分位取均值': $num = $data['data']['htiqn8gitk'];  break;
      case '最小值': $num = $data['data']['htiqncdra2'];  break;
      
      default:
        # code...
        break;
    } 
    return $num;
  }
  function rm_ckcs()
  {
    $sid = WEBSID;
    $uuid = $_POST['uuid'];
    $sql = "UPDATE `entity` SET `del` = 1 WHERE `type` = 'guzhi_ckcs' AND `_rel` = '{$uuid}' AND `sid` = '{$sid}'";
    $this->em->db->query($sql);
    $arr['msg'] = '成功';
    ajax_return($arr);
  }
  /**
   *  task 2024-12866 创谷：将估值结果反带回概况中的财务口径估值模块
   *  https://oa.vc800.com/?/flow/view/htiljc57si
   *  陈延阳 yychen@pepm.com.cn
   *  v6.7.1
   */
  function guzhi_fandai($eid){
    $info = $this->em->get_one($eid);
    $info['data'] = _decode($info['data']);
    if($info['data']['hs4z5243c2'] == '是'){
      $old = $cashflow_cwbggz = $this->em->get("AND `del` = 0 AND `type` = 'cashflow_cwbggz' AND `data`->>'$.xmgz_uuid' = '{$eid}'")[0];
      $cashflow_cwbggz['type'] = 'cashflow_cwbggz';
      $cashflow_cwbggz['data'] = $info['data'];
      $cashflow_cwbggz['data']['h1o0nz67u0'] = $info['data']['h24b9vxbed'];// 基准日剩余成本
      $cashflow_cwbggz['data']['h1o0nzokxb'] = $info['data']['gfpgl2jx71'];// 估值金额
      $cashflow_cwbggz['data']['h1o0okww15'] = $info['data']['h89510l4a9'];// 增减值
      $cashflow_cwbggz['data']['h1o0olid34'] = $info['data']['h8950tefzc'];// 增减值率
      $cashflow_cwbggz['data']['xmgz_uuid'] = $eid;// 增减值率
      if($old){
        $this->em->replace($cashflow_cwbggz,$cashflow_cwbggz['type'],'update',false,true,true,false);
      }else{
        $this->em->replace($cashflow_cwbggz,$cashflow_cwbggz['type'],'add',false,true,true,false);
      }
      
    }
  }
  function guzhi_fandai_del($eid){
    $old = $this->em->get("AND `del` = 0 AND `type` = 'cashflow_cwbggz' AND `data`->>'$.xmgz_uuid' = '{$eid}'")[0];
    if($old){
      $old['data'] = _decode($old['data']);
      $old['del'] = 1;
      $this->em->replace($old,$old['type'],'update',false,false,false,false);
    }
  }
  function cron_guzhi(){
    $sid = WEBSID;
    $cron_guzhi = $this->cm->key('cron_guzhi');
    if($cron_guzhi[0]){
      $uuid = $cron_guzhi[0]['uuid'];
      $this->create_guzhis($uuid,$cron_guzhi[0]['nian'], $cron_guzhi[0]['jidu'],$cron_guzhi[0]['jizhunri']);
      unset($cron_guzhi[0]);
      $cron_guzhis = array();
      foreach ($cron_guzhi as $key => $value) {
        $cron_guzhis[] = $value;
      }
      $d_str = _encode($cron_guzhis);
      $sql = "UPDATE config SET `data` = '{$d_str}' WHERE `sid` = '{$sid}' and `key` = 'cron_guzhi'";
      $this->em->db->query($sql);
    }else{
      
    }
    
  }
  function cron_create_guzhi(){
    $sid = WEBSID;
    $uuid = $_POST['uuid'];
    $nian = $_POST['time'];// 年
    $jidu = $_POST['jidu'];// 季度
    $jizhunri = str_replace('/','-',$_POST['jizhunri']);// 基准日
    $uuid_str = implode("','",$uuid);
    $company = $this->em->get("AND `uuid` in ('{$uuid_str}') AND `type` = 'company' AND `del` = 0");
    $cron_guzhi = $this->cm->key('cron_guzhi');
    foreach ($company as $key => $value) {
      $value['data'] = _decode($value['data']);
      $gzff = array('市场法-市盈率','市场法-市销率','市场法-市净率','市场价格调整法(BS)','市场价格调整法(AAP)','收益法-现金流折现法');
      if(in_array($value['data']['h1o0nyoav0'],$gzff)){
        $arr['uuid'] = $value['uuid'];
        $arr['nian'] = $_POST['time'];
        $arr['jidu'] = $_POST['jidu'];
        $arr['jizhunri'] = $jizhunri;
        $cron_guzhi[] = $arr;
      }else{
        $this->create_guzhis($value['uuid'],$nian,$jidu,$jizhunri);
      }
      
    }
    $d_str = _encode($cron_guzhi);
    $sql = "UPDATE config SET `data` = '{$d_str}' WHERE `sid` = '{$sid}' and `key` = 'cron_guzhi'";
    $this->em->db->query($sql);
  }
  function create_guzhis($uuid = '',$nian = '' , $jidu = '',$jizhunri = '')
  { 
    $api = load('c/api');
    // 计算估值
    // 获取 xiangmuguzhi 配置
    $xiangmuguzhi_config = $this->cm->key('xiangmuguzhi')['item'];
    // 获取 对应uuid 的数据
    // dump($uuid_str);
    $company = $this->em->get("AND `uuid` = '{$uuid}' AND `type` = 'company' AND `del` = 0");
    // $company = $this->em->get("AND `uuid` = '{$uuid}' AND `type` = 'company' AND `del` = 0");
    // dump($company);
    $config = array_keys($xiangmuguzhi_config);
    
    foreach ($company as $key => $value) {
      $arr = $data = array();
      $value['data'] = _decode($value['data']);
      $value_key = array_keys($value['data']);
      foreach ($config as $k => $v) {
        if(in_array($v,$value_key)){
          $arr[$v] = $value['data'][$v];
          if($xiangmuguzhi_config[$v]['type'] == 'select_new'){
            $arr[$v.'_label'] = $value['data'][$v.'_label'];
          }
        }
      }
      $arr['h1o0ncqdho'] = $jizhunri;
      $arr['gznf'] = $nian;
      $arr['gzjd'] = $arr['gzjd_label'] = $jidu;
      $arr['screenName'] = $value['data']['fullname'];
      $arr['company'] = $value['uuid'];
      $arr['company_label'] = $value['data']['name'];  
      // 获取上次的估值公允价值
      $pre_gz = $this->em->get("AND `type` = 'xiangmuguzhi' AND `del` = 0 AND `data`->>'$.company' = '{$arr['company']}' AND `data`->>'$.fund' = '{$arr['fund']}' order by `data`->>'$.h1o0ncqdho' desc")[0];
      $arr['bcgzqgyjz'] = _decode($pre_gz['data'])['gfpgl2jx71']; // 本次估值前公允价值 bcgzqgyjz
      $cashflow_portfolio = $this->em->get("AND `type` = 'cashflow_portfolio' AND `del` = 0 AND `data`->>'$.company' = '{$value['uuid']}' AND `data`->>'$.fund' = '{$value['data']['fund']}' AND `data`->>'$.fukuanshijian' <= '{$jizhunri}'");
      $arr['fvf2axxeoo'] = 0;
      $arr['gwftl5bgwl'] = 0;
      $arr['h24b9vxbed'] = 0;
      $arr['gtm4p6u9b5'] = 0;
      $arr['xmzsr'] = 0;
      foreach ($cashflow_portfolio as $k => $v) {
        $v['data'] = _decode($v['data']);
        switch ($v['data']['cf_type']) { 
          case '(流出)投资款':$arr['fvf2axxeoo'] += $v['data']['fukuanjine']; break;
          case '(流入)退出款':
            $arr['gwftl5bgwl'] += $v['data']['h117obgb3r']; 
            $arr['gtm4p6u9b5'] += $v['data']['h117ocajr7'];
            $arr['xmzsr'] += $v['data']['fukuanjine']; 
            break;
          case '(流入)持有期间收益':$arr['xmzsr'] += $v['data']['fukuanjine']; break;
          case '(流入)其他收益':$arr['xmzsr'] += $v['data']['fukuanjine']; break;
          default:
            # code...
            break;
        }
      }
      $arr['h24b9vxbed'] = $arr['fvf2axxeoo'] - $arr['gwftl5bgwl'];
      $jzr = strtotime($jizhunri);
      $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}' AND `data`->>'$.htcwgr8bmy' <= '{$jzr}' ORDER BY `data`->>'$.htcwgr8bmy' DESC")[0];
      $company_caiwu1['data'] = _decode($company_caiwu1['data']);
      $arr['hsveec2613'] = $company_caiwu1['data']['hsveec2613'];
      $arr['hsvefurzkn'] = $company_caiwu1['data']['hsvefurzkn'];
      $arr['fsz6tqz4gz'] = $company_caiwu1['data']['fsz6tqz4gz']; 
      $arr['gwfvy19k7r'] = $company_caiwu1['data']['gwfvy19k7r'];
      $arr['gwfvy0y65t'] = $company_caiwu1['data']['gwfvy0y65t'];
      $arr['huig3o97c3'] = round($company_caiwu1['data']['gwfvy19k7r'] / $company_caiwu1['data']['gwfvy0y65t'],4) * 100;  // task 2024-14497

      // 判断估值方法
      switch ($value['data']['h1o0nyoav0']) {
        case '成本法-投资成本':
          // 本次公允价值 gfpgl2jx71 = 项目池中的累计投资金额 fvf2axxeoo 
          $arr['gfpgl2jx71'] = $arr['fvf2axxeoo'];
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未 退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允d值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '成本法-净资产':
          // 项目池中，穿透到项目的二级菜单财务数据模块company_caiwu1，取最新一条财务数据的 归属于母公司股东权益 gwfvy0y65t 的值即可。
          // $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}' order by `data`->>'$.gu25l8ddzs' desc")[0];
          $arr['gfpgl2jx71'] = $arr['gwfvy0y65t'] * $value['data']['h38yx4pf9a'] / 100;
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOCm
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],4) * 100; // 本次估值波动率
          break;
        case '市场法-近期交易法(融资）':
          // 项目池中的当前持股比例 h38yx4pf9a * 融资后企业整体估值 gwfvkm7ev0
          $qyrzqk = $this->em->get("AND `type` = 'qyrzqk' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}' AND `data`->>'$.fund' = '{$value['data']['fund']}' AND `data`->>'$.fi7j65guxn' <= '{$jizhunri}'  order by `data`->>'$.fi7j65guxn' desc")[0];
          $qyrzqk['data'] = _decode($qyrzqk['data']);
          $arr['h38yx4pf9a'] = $qyrzqk['data']['gwfve1ejq8'];
          $arr['fi7j65guz5'] = $qyrzqk['data']['fi7j65guz5'];
          $arr['h0dulxngeo'] = $qyrzqk['data']['h0dulxngeo'];
          $arr['gwfvkm7ev0'] = $qyrzqk['data']['gwfvkm7ev0'];
          $arr['fi7j65guxn'] = $qyrzqk['data']['fi7j65guxn'];
          $arr['gfpgl2jx71'] = ($arr['h38yx4pf9a'] / 100) *  $qyrzqk['data']['gwfvkm7ev0'];
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '收益法-现金流折现法':
          break;
        case '市值法': 
          // 本次公允价值=估值基准日那天的收盘价格*当前持股数量
          // 估值基准日那天的收盘价格：收盘价格需要根据已投项目的股票代码gtcl9c7k5s字段去抓取外部股价数据，如果估值基准日那天是非交易日，则向前取数，比如估值基准日是2024年9月30日，这天是周日，没有收盘价，就取前一天的数据为收盘价格，如果前一天也没有收盘价格，就继续往前一天取。
          // 当前持股数量gi1xt7zuig：已投项目池概况字段
          $code = $value['data']['gtcl9c7k5s'];
          // 判断距离 基准日最近的一个工作日
          $spjg = 0;
          for ($i=0; $i < 15; $i++) { 
            $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
            $a = load('c/api')->flow_getStockMarketDataByDate($code,str_replace('/','-',$date));
            if(!empty($a['data']['data']) ){
              $spjg = $a['data']['data']['close'];
              break;
            }
          }
          $arr['spjg'] = $spjg;
          $arr['gfpgl2jx71'] = $spjg * $value['data']['gi1xt7zuig'];
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '临近IPO公司股票市值法':
          // 本次公允价值=当前持股数量*股票发行价*（1-缺乏流动性折扣经验值）
          $arr['gfpgl2jx71'] = $value['data']['gi1xt7zuig'] * $value['data']['hss57vl9wh'] * (1 - $value['data']['hsn9lxfzrl'] / 100);
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '临近IPO公司股票市值法(AAP)':
          // T：剩余限售期（年）=限售期天数/365  限售期天数=IPO后预计解禁日期 hst8clt4sf-估值基准日（假设共10天）
          $xsq = round((strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
          $xsqts = (strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
          if($value['data']['htrz8pbfjz'] == '个股股价'){
            $arr['get_gegu'] = round($api->get_gegu($xsqts,$jizhunri,$value['data']['gtcl9c7k5s'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
            $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
            $arr['three'] = $three = round($this->normSdist($two/2),4);
            $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
            // $five = round(exp(-$value['date']['']),2)
            $arr['five'] = $five = round(1 * exp((($value['data']['htt3bbe5ht']/100) - ($value['data']['htt3bbe5ht']/100) * 2) * $xsq) * ($three - $four),4);
            // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
            // $arr['spjg'] = $bdgj = 0;
            // $code = $value['data']['gtcl9c7k5s'];
            // $date = $jizhunri;
            // for ($i=0; $i < 15; $i++) { 
            //   $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
            //   $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
            //   if(!empty($a['data']['data']) ){
            //     $arr['spjg'] = $bdgj = $a['data']['data']['close'];
            //     break;
            //   }
            // }
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            $arr['gfpgl2jx71'] = $value['data']['hss57vl9wh'] * ( 1 - $five) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['five'] * 100; // 流动性折扣率
          }elseif($value['data']['htrz8pbfjz'] == '行业股价'){
            $arr['get_gegu'] = round($api->get_hygj($xsqts,$jizhunri,$value['data']['ejhy'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
            $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
            $arr['three'] = $three = round($this->normSdist($two/2),4);
            $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
            // $five = round(exp(-$value['date']['']),2)
            $arr['five'] = $five = round(1 * exp((($value['data']['htt3bbe5ht']/100) - ($value['data']['htt3bbe5ht']/100) * 2) * $xsq) * ($three - $four),4);
            // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            // $arr['spjg'] = $bdgj = 0;
            // $code = $value['data']['gtcl9c7k5s'];
            // $date = $jizhunri;
            // for ($i=0; $i < 15; $i++) { 
            //   $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
            //   $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
            //   if(!empty($a['data']['data']) ){
            //     $arr['spjg'] = $bdgj = $a['data']['data']['close'];
            //     break;
            //   }
            // }
            $arr['gfpgl2jx71'] = $value['data']['hss57vl9wh'] * ( 1 - $five) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['five'] * 100; // 流动性折扣率
          }else{
            continue;
          }
          // S：估值基准日那天的行业平均收盘价格=估值基准日对应的交易日所有二级行业项目的收盘价格的平均数（可以直接为1计算）
          $pjsp = 1;
          break;
        case '临近IPO公司股票市值法(BS)':
          // T：剩余限售期（年）=限售期天数/365  限售期天数=IPO后预计解禁日期 hst8clt4sf-估值基准日（假设共10天）
          $xsq = round((strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
          $xsqts = (strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
          //  获取 rf
          $rf = $this->em->get("AND `type` = 'guzhi_gzsyl' AND `del` = 0 AND `data`->>'$.htjlkwbanp' = '{$jizhunri}'")[0];
          $rf['data'] = _decode($rf['data']);
          $guzhi_gzsyl_n =array(
            '0.00' => 'htjkye8wbn',
            '0.08' => 'htjkyenwk8',
            '0.17' => 'htrycw0bsa',
            '0.25' => 'htjkyf9kwr',
            '0.50' => 'htjkyfocqp',
            '0.75' => 'htjkyg22jh',
            '1.00' => 'htjkyge9l2',
            '2.00' => 'htjkygr5ci',
            '3.00' => 'htjkyh2ygb',
            '5.00' => 'htjkyhfcnm',
            '7.00' => 'htjkyhqml4',
            '10.00' => 'htjl0tpben',
            '15.00' => 'htjl0u6hu7',
            '20.00' => 'htjl0ujxec',
            '30.00' => 'htjl0uxd31', 
            '40.00' => 'htjl0v9gdh',
            '50.00' => 'htjl0vmgci'
          );
          $guzhi_gzsyl_num = 0;
          if(in_array($xsq,array_keys($guzhi_gzsyl_n))){
            $guzhi_gzsyl_num = $guzhi_gzsyl_n[$xsq] /100;
          }else{
            $min = 0;
            $max = 0;
            foreach (array_keys($guzhi_gzsyl_n) as $q => $w) {
              if($w < $xsq){ 
                $min = $w;
              }else{
                $max = $w;
                break;
              }
              
            }
            $min_val = $rf['data'][$guzhi_gzsyl_n[$min]]; 
            $max_val = $rf['data'][$guzhi_gzsyl_n[$max]];
              $guzhi_gzsyl_num = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($xsq - $min) + $min_val,4) / 100;
          }
          if($value['data']['htrz8pbfjz'] == '个股股价'){
            $arr['get_gegu'] = round($api->get_gegu($xsqts,$jizhunri,$value['data']['gtcl9c7k5s'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            // ①=EX*EXP( - rf *t )
            $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
            // ②=σ*t^0.5
            $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
            // (LN(P/EX)+(rf+σ*σ/2)*t)/②
            $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
            // ④=③-②
            $arr['four'] = $four = round($three - $two,4);
            // ⑤=NORMDIST(④,0,1,TRUE)*①
            $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
            // ⑥=NORMDIST(③,0,1,TRUE)*P   
            $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
            // ⑦=⑤-⑥
            $arr['seven'] = $seven = round($five - $six,4);
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            // $arr['spjg'] = $bdgj = 0;
            // $code = $value['data']['gtcl9c7k5s'];
            // $date = $jizhunri;
            // for ($i=0; $i < 15; $i++) { 
            //   $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
            //   $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
            //   if(!empty($a['data']['data']) ){
            //     $arr['spjg'] = $bdgj = $a['data']['data']['close'];
            //     break;
            //   }
            // }
            $arr['gfpgl2jx71'] = $value['data']['hss57vl9wh'] * ( 1 - $seven) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['seven'] * 100; // 流动性折扣率
          }elseif($value['data']['htrz8pbfjz'] == '行业股价'){
            $arr['get_gegu'] = round($api->get_hygj($xsqts,$jizhunri,$value['data']['ejhy'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            // ①=EX*EXP( - rf *t )
            $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
            // ②=σ*t^0.5
            $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
            // (LN(P/EX)+(rf+σ*σ/2)*t)/②
            $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
            // ④=③-②
            $arr['four'] = $four = round($three - $two,4);
            // ⑤=NORMDIST(④,0,1,TRUE)*①
            $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
            // ⑥=NORMDIST(③,0,1,TRUE)*P   
            $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
            // ⑦=⑤-⑥
            $arr['seven'] = $seven = round($five - $six,4);
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            // $arr['spjg'] = $bdgj = 0;
            // $code = $value['data']['gtcl9c7k5s'];
            // $date = $jizhunri;
            // for ($i=0; $i < 15; $i++) { 
            //   $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
            //   $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
            //   if(!empty($a['data']['data']) ){
            //     $arr['spjg'] = $bdgj = $a['data']['data']['close'];
            //     break;
            //   }
            // }
            $arr['gfpgl2jx71'] = $value['data']['hss57vl9wh'] * ( 1 - $seven) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['seven'] * 100; // 流动性折扣率
          }else{
            continue;
          }
          break;
        case '市场法-市净率':
          // 本次公允价值=企业归母净资产*可比公司的平均PB值*当前持股比例*流动性折扣率 
          // 企业归母净资产：根据项目穿透到财务报表模块 company_caiwu1 ，取财务报表中的归属于母公司股东权益 gwfvy0y65t 字段的数据
          // 可比上市公司的平均PB值：根据项目的万得三级行业 sanjhy ，去上市公司项目库 sgsjcsjk 中，查询出同三级行业的项目，把这些项目的PB值全部获取到，然后计算平均值，就是可比上市公司的平均PB值了
          // 流动性折扣率：已投项目池概况中的字段 hsn9lxfzrl
          $htcwgr8bmy = strtotime($jizhunri);
          
          $sid = WEBSID;
          // $sql = "select * from `fengchao` where 1 and `sanjhy` = '{$value['data']['sanjhy']}' limit 0, 10000";
          $sql = "select * from `entity` where 1 and del = 0 and `type` = 'kbssgs_cd' and sid = '{$sid}' and `data`->>'$.sanjhy' = '{$value['data']['sanjhy']}' and `data`->>'$.gfpgkl8loq' = '{$jizhunri}' limit 0, 10000";
          $sgsjcsjk = $this->em->db->query($sql);
          $gtcl9c7k5s_pj = 0;
          $shijinglv = 0;
          $shijinglv_num = 0;
          foreach ($sgsjcsjk as $k => $v) {
            $v['data'] = _decode($v['data']);
            // if($v['shijinglv'] != 0 && $v['shijinglv'] != ''){
            $shijinglv_num++;
            $shijinglv += $v['data']['shijinglv'];
            // }
          }
          if($shijinglv_num){
            $gtcl9c7k5s_pj = round($shijinglv / $shijinglv_num,4);
          }else{
            $gtcl9c7k5s_pj = 0;
          }
          $arr['hsn9gxm3kv'] = $gtcl9c7k5s_pj;
          $arr['hss4x82exz'] = '三级行业';
          $arr['hss4x82exz_label'] = '三级行业';
          $arr['hss2gu7dv6'] = '平均值';
          $arr['hss2gu7dv6_label'] = '平均值';
          $arr['gfpgl2jx71'] = $company_caiwu1['data']['gwfvy0y65t'] * $gtcl9c7k5s_pj * (1-$value['data']['hsn9lxfzrl'] / 100) * ($value['data']['h38yx4pf9a'] / 100);
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break; 
        case '市场法-市盈率':
          // 本次公允价值=企业归母净利润*可比公司的平均PE值*当前持股比例*流动性折扣率
          // 企业归母净资产：根据项目穿透到财务报表模块 company_caiwu1 ，取财务报表中的归属于母公司股东权益 gwfvy0y65t 字段的数据
          // 可比上市公司的平均PB值：根据项目的万得三级行业 sanjhy ，去上市公司项目库 sgsjcsjk 中，查询出同三级行业的项目，把这些项目的PB值全部获取到，然后计算平均值，就是可比上市公司的平均PB值了
          // 流动性折扣率：已投项目池概况中的字段hsn9lxfzrl
          $htcwgr8bmy = strtotime($jizhunri);
          // $company_caiwu1 = $this->em->get("AND `type` = 'company_caiwu1' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}' AND `data`->>'$.htcwgr8bmy' <= '{$htcwgr8bmy}' ORDER BY `data`->>'$.htcwgr8bmy' DESC")[0];
          // $company_caiwu1['data'] = _decode($company_caiwu1['data']);
          $sid = WEBSID;
          // $sql = "select * from `fengchao` where 1 and `sanjhy` = '{$value['data']['sanjhy']}' limit 0, 10000";
          $sql = "select * from `entity` where 1 and del = 0 and `type` = 'kbssgs_cd' and sid = '{$sid}' and `data`->>'$.sanjhy' = '{$value['data']['sanjhy']}' and `data`->>'$.gfpgkl8loq' = '{$jizhunri}' limit 0, 10000";
          $sgsjcsjk = $this->em->db->query($sql);
          $gtcl9c7k5s_pj = 0;
          $shiyinglv = 0;
          $shiyinglv_num = 0;
          foreach ($sgsjcsjk as $k => $v) {
            $v['data'] = _decode($v['data']);
            // if($v['shiyinglv'] != 0 && $v['shiyinglv'] != ''){
              $shiyinglv_num++;
              $shiyinglv += $v['data']['shiyinglv'];
            // }
          }
          if($shiyinglv_num){
            $arr['hsn9hcw256'] = $gtcl9c7k5s_pj = round($shiyinglv / $shiyinglv_num,4);
          }else{
            $arr['hsn9hcw256'] = $gtcl9c7k5s_pj = 0;
          }
          $arr['hss4x82exz'] = '三级行业';
          $arr['hss4x82exz_label'] = '三级行业';
          $arr['hss2gu7dv6'] = '平均值';
          $arr['hss2gu7dv6_label'] = '平均值';
          $arr['hsn9hcw256'] = $gtcl9c7k5s_pj;
          $arr['gfpgl2jx71'] = $company_caiwu1['data']['gwfvy19k7r'] * $gtcl9c7k5s_pj * (1-$value['data']['hsn9lxfzrl'] / 100) * ($value['data']['h38yx4pf9a'] / 100);
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;  
        case '市场法-市销率':
          // 本次公允价值=企业归母净利润*可比公司的平均PE值*当前持股比例*流动性折扣率
          // 企业归母净资产：根据项目穿透到财务报表模块 company_caiwu1 ，取财务报表中的归属于母公司股东权益 gwfvy0y65t 字段的数据
          // 可比上市公司的平均PB值：根据项目的万得三级行业 sanjhy ，去上市公司项目库 sgsjcsjk 中，查询出同三级行业的项目，把这些项目的PB值全部获取到，然后计算平均值，就是可比上市公司的平均PB值了
          // 流动性折扣率：已投项目池概况中的字段hsn9lxfzrl
          $htcwgr8bmy = strtotime($jizhunri);
          $sid = WEBSID;
          // $sql = "select * from `fengchao` where 1 and `sanjhy` = '{$value['data']['sanjhy']}' limit 0, 10000";
          $sql = "select * from `entity` where 1 and del = 0 and `type` = 'kbssgs_cd' and sid = '{$sid}' and `data`->>'$.sanjhy' = '{$value['data']['sanjhy']}' and `data`->>'$.gfpgkl8loq' = '{$jizhunri}' limit 0, 10000";
          $sgsjcsjk = $this->em->db->query($sql);
          $gtcl9c7k5s_pj = 0;
          $shixiaolv = 0;
          $shixiaolv_num = 0;
          foreach ($sgsjcsjk as $k => $v) {
            // if($v['shixiaolv'] != 0 && $v['shixiaolv'] != ''){
              $v['data'] = _decode($v['data']);
              $shixiaolv_num++;
              $shixiaolv += $v['data']['shixiaolv'];
            // }
          }
          if($shixiaolv_num){
            $gtcl9c7k5s_pj = round($shixiaolv / $shixiaolv_num,4);
          }else{
            $gtcl9c7k5s_pj = 0;
          }
          $arr['hsn9hfpyq4'] = $gtcl9c7k5s_pj;
          $arr['hss4x82exz'] = '三级行业';
          $arr['hss4x82exz_label'] = '三级行业';
          $arr['hss2gu7dv6'] = '平均值';
          $arr['hss2gu7dv6_label'] = '平均值';
          $arr['gfpgl2jx71'] = $company_caiwu1['data']['fsz6tqz4gz'] * $gtcl9c7k5s_pj * (1-$value['data']['hsn9lxfzrl'] / 100) *  ($value['data']['h38yx4pf9a'] / 100);
          $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
          $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
          $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
          break;
        case '市场价格调整法(AAP)':
          // T：剩余限售期（年）=限售期天数/365  限售期天数=IPO后预计解禁日期 hst8clt4sf-估值基准日（假设共10天）
          $xsq = round((strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
          $xsqts = (strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
          if($value['data']['htrz8pbfjz'] == '个股股价'){
            $arr['get_gegu'] = round($api->get_gegu($xsqts,$jizhunri,$value['data']['gtcl9c7k5s'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
            $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
            $arr['three'] = $three = round($this->normSdist($two/2),4);
            $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
            // $five = round(exp(-$value['date']['']),2)
            $arr['five'] = $five = round(1 * exp((($value['data']['htt3bbe5ht']/100) - ($value['data']['htt3bbe5ht']/100) * 2) * $xsq) * ($three - $four),4);
            // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
            //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            $arr['spjg'] = $bdgj = 0;
            $code = $value['data']['gtcl9c7k5s'];
            $date = $jizhunri;
            for ($i=0; $i < 15; $i++) { 
              $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
              $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
              if(!empty($a['data']['data']) ){
                $arr['spjg'] = $bdgj = $a['data']['data']['close'];
                break;
              }
            }
            $arr['gfpgl2jx71'] = $bdgj * ( 1 - $five) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['five'] * 100; // 流动性折扣率
          }elseif($value['data']['htrz8pbfjz'] == '行业股价'){
            $arr['get_gegu'] = round($api->get_hygj($xsqts,$jizhunri,$value['data']['ejhy'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            $arr['one'] = $one = round(pow($bodonglv,2) * $xsq,4);
            $arr['two'] = $two = round(pow($one + log(2 * (exp($one) - $one -1)) - 2 * log((exp($one) - 1)),0.5),4);
            $arr['three'] = $three = round($this->normSdist($two/2),4);
            $arr['four'] = $four = round($this->normSdist(($two - 2 * $two)/2),4);
            // $five = round(exp(-$value['date']['']),2)
            $arr['five'] = $five = round(1 * exp((($value['data']['htt3bbe5ht']/100) - ($value['data']['htt3bbe5ht']/100) * 2) * $xsq) * ($three - $four),4);
            // 估值基准日那天的收盘价格（标的自身股价）*（1-缺乏流动性折扣（%））*当前持股数量
            //$arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            $arr['spjg'] = $bdgj = 0;
            $code = $value['data']['gtcl9c7k5s'];
            $date = $jizhunri;
            for ($i=0; $i < 15; $i++) { 
              $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
              $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
              if(!empty($a['data']['data']) ){
                $arr['spjg'] = $bdgj = $a['data']['data']['close'];
                break;
              }
            }
            $arr['gfpgl2jx71'] = $bdgj * ( 1 - $five) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['five'] * 100; // 流动性折扣率
          }else{
            continue;
          }
          // S：估值基准日那天的行业平均收盘价格=估值基准日对应的交易日所有二级行业项目的收盘价格的平均数（可以直接为1计算）
          $pjsp = 1;
          break;
        case '市场价格调整法(BS)':
          // T：剩余限售期（年）=限售期天数/365  限售期天数=IPO后预计解禁日期 hst8clt4sf-估值基准日（假设共10天）
          $xsq = round((strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
          $xsqts = (strtotime($value['data']['hst8clt4sf']) - strtotime($jizhunri)) / 86400;
          //  获取 rf
          $rf = $this->em->get("AND `type` = 'guzhi_gzsyl' AND `del` = 0 AND `data`->>'$.htjlkwbanp' = '{$jizhunri}'")[0];
          $rf['data'] = _decode($rf['data']);
          $guzhi_gzsyl_n =array(
            '0.00' => 'htjkye8wbn',
            '0.08' => 'htjkyenwk8',
            '0.17' => 'htrycw0bsa',
            '0.25' => 'htjkyf9kwr',
            '0.50' => 'htjkyfocqp',
            '0.75' => 'htjkyg22jh',
            '1.00' => 'htjkyge9l2',
            '2.00' => 'htjkygr5ci',
            '3.00' => 'htjkyh2ygb',
            '5.00' => 'htjkyhfcnm',
            '7.00' => 'htjkyhqml4',
            '10.00' => 'htjl0tpben',
            '15.00' => 'htjl0u6hu7',
            '20.00' => 'htjl0ujxec',
            '30.00' => 'htjl0uxd31', 
            '40.00' => 'htjl0v9gdh',
            '50.00' => 'htjl0vmgci'
          );
          $guzhi_gzsyl_num = 0;
          if(in_array($xsq,array_keys($guzhi_gzsyl_n))){
						$guzhi_gzsyl_num = $guzhi_gzsyl_n[$xsq] / 100;
					}else{
						$min = 0;
						$max = 0;
						foreach (array_keys($guzhi_gzsyl_n) as $q => $w) {
							if($w < $xsq){
								$min = $w;
							}else{
								$max = $w;
								break;
							}
							
						}
						$min_val = $rf['data'][$guzhi_gzsyl_n[$min]]; 
						$max_val = $rf['data'][$guzhi_gzsyl_n[$max]];
 						$guzhi_gzsyl_num = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($xsq - $min) + $min_val,4) /100;
					}
          if($value['data']['htrz8pbfjz'] == '个股股价'){
            $arr['get_gegu'] = round($api->get_gegu($xsqts,$jizhunri,$value['data']['gtcl9c7k5s'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            // ①=EX*EXP( - rf *t )
            $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
            // ②=σ*t^0.5
            $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
            // (LN(P/EX)+(rf+σ*σ/2)*t)/②
            $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
            // ④=③-②
            $arr['four'] = $four = round($three - $two,4);
            // ⑤=NORMDIST(④,0,1,TRUE)*①
            $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
            // ⑥=NORMDIST(③,0,1,TRUE)*P   
            $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
            // ⑦=⑤-⑥
            $arr['seven'] = $seven = round($five - $six,4);
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            $arr['spjg'] = $bdgj = 0;
            $code = $value['data']['gtcl9c7k5s'];
            $date = $jizhunri;
            for ($i=0; $i < 15; $i++) { 
              $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
              $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
              if(!empty($a['data']['data']) ){
                $arr['spjg'] = $bdgj = $a['data']['data']['close'];
                break;
              }
            }
            $arr['gfpgl2jx71'] = $bdgj * ( 1 - $seven) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['seven'] * 100; // 流动性折扣率
          }elseif($value['data']['htrz8pbfjz'] == '行业股价'){
            $arr['get_gegu'] = round($api->get_hygj($xsqts,$jizhunri,$value['data']['ejhy'],$value['data']['hst8clt4sf']),4);
            $bodonglv = round($arr['get_gegu'] * sqrt(250),4);
            // ①=EX*EXP( - rf *t )
            $arr['one'] = $one = round(1 * exp(($guzhi_gzsyl_num - $guzhi_gzsyl_num*2) * $xsq),4);
            // ②=σ*t^0.5
            $arr['two'] = $two = round($bodonglv * pow($xsq,0.5),4);
            // (LN(P/EX)+(rf+σ*σ/2)*t)/②
            $arr['three'] = $three = round((log(1) + ($guzhi_gzsyl_num + $bodonglv * $bodonglv / 2) * $xsq) / $two,4);
            // ④=③-②
            $arr['four'] = $four = round($three - $two,4);
            // ⑤=NORMDIST(④,0,1,TRUE)*①
            $arr['five'] = $five = round($this->normSdist($four - (2 * $four)) * $one,4);
            // ⑥=NORMDIST(③,0,1,TRUE)*P   
            $arr['six'] = $six = round($this->normSdist($three - (2 * $three)) * 1,4);
            // ⑦=⑤-⑥
            $arr['seven'] = $seven = round($five - $six,4);
            // $arr['spjg'] = $bdgj = $api->flow_getStockMarketDataByDate($value['data']['gtcl9c7k5s'],$jizhunri)['data']['data']['close'];
            $arr['spjg'] = $bdgj = 0;
            $code = $value['data']['gtcl9c7k5s'];
            $date = $jizhunri;
            for ($i=0; $i < 15; $i++) { 
              $date = date('Y-m-d',strtotime($jizhunri) - 86400 * $i);
              $a = load('c/api')->flow_getStockMarketDataByDate($code,$date);
              if(!empty($a['data']['data']) ){
                $arr['spjg'] = $bdgj = $a['data']['data']['close'];
                break;
              }
            }
            $arr['gfpgl2jx71'] = $bdgj * ( 1 - $seven) * $value['data']['gi1xt7zuig'];
            $arr['h8950owxca'] = round($arr['gfpgl2jx71'] / $arr['h24b9vxbed'],4); // 未退出部分MOC
            $arr['h89510l4a9'] = $arr['gfpgl2jx71'] - $arr['bcgzqgyjz']; // 本次公允价值波动 
            $arr['h8950tefzc'] = round($arr['h89510l4a9'] / $arr['bcgzqgyjz'],6) * 100; // 本次估值波动率
            $arr['hsn9lxfzrl'] = $arr['seven'] * 100; // 流动性折扣率
          }else{
            continue;
          }
          break;
        default:
          # code...
          break;
      }
      $data['data'] = $arr;
      $data['type'] = 'xiangmuguzhi';
      $res = $this->em->replace($data,"xiangmuguzhi","add",false,false,false,false);
      switch ($value['data']['h1o0nyoav0']) {
        case '市场法-市净率':
        case '市场法-市盈率':
        case '市场法-市销率':
          // 获取对应三行业所有数据
          $jzr_s = $jizhunri;
          // $sql = "select * from `fengchao` where 1 and `sanjhy` = '{$value['data']['sanjhy']}' and `gfpgkl8loq` = '{$jzr_s}' limit 0, 10000";
          $sql = "select * from `entity` where 1 and `del` = 0 and `type` = 'kbssgs_cd' and `data`->>'$.ejhy' = '{$value['data']['ejhy']}' and `data`->>'$.gfpgkl8loq' = '{$jzr_s}' and `sid` = '{$sid}' limit 0, 10000";
          $sgsjcsjk = $this->em->db->query($sql);
					$sxl_arr = array();
					$syl_arr = array();
					$sjl_arr = array();
          $ttm_arr = array();
					$s_num = 0;
          $sgsjcsjk_ejhy = array();
          $sgsjcsjk_sanjhy = array();
          foreach ($sgsjcsjk as $k => $v) {
            $v['data'] = _decode($v['data']);
            $kbssgs = array();
            $kbssgs['sid'] = WEBSID;
            $kbssgs['data'] = $v['data'];
            $kbssgs['data']['_rel'] = $res;
            if($v['data']['sanjhy'] == $value['data']['sanjhy']){
              $s_num++;
              if($v['data']['shixiaolv'] >= 0 && is_numeric($v['data']['shixiaolv']))$sxl_arr[] = $v['data']['shixiaolv'];
              if($v['data']['shiyinglv'] >= 0 && is_numeric($v['data']['shiyinglv']))$syl_arr[] = $v['data']['shiyinglv'];
              if($v['data']['shijinglv'] >= 0 && is_numeric($v['data']['shijinglv']))$sjl_arr[] = $v['data']['shijinglv'];
              if($v['data']['roettm'] >= 0 && is_numeric($v['data']['roettm']))$ttm_arr[] = $v['data']['roettm'];
              $kbssgs['type'] = 'kbssgs';
              $sgsjcsjk_sanjhy[] = $kbssgs;
              $kbssgs['type'] = 'kbssgs3';
              $sgsjcsjk_ejhy[] = $kbssgs;
              // $this->em->replace($kbssgs,"kbssgs","add",false,false,false,false);
            }else{
              $kbssgs['type'] = 'kbssgs';
              $sgsjcsjk_ejhy[] = $kbssgs;
            }
            
          }
          $this->em->bunch_add($sgsjcsjk_sanjhy);
          $this->em->bunch_add($sgsjcsjk_ejhy);

					$ckcs['type'] = 'guzhi_ckcs';
          // 最大值
          sort($sxl_arr);
          $ckcs['data']['htiqnfqzdj'] = '市场法-市销率';
          $ckcs['data']['htiqnfqzdj_label'] = '市场法-市销率';
          $last = count($sxl_arr);
          $ckcs['data']['htiqlsmdbc'] = round($sxl_arr[$last-1],4); // 最大值
          // 第三四分位
          $fen = 3 * $last / 4;
          if(floor($fen) == $fen){
            $san_si = round(($sxl_arr[$fen-1] + $sxl_arr[$fen]) / 2,4);
          }else{ 
            $san_si = $sxl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
          $ckcs['data']['htiqltiooq'] = round($san_si,4); // 第三四分位取均值
          $all = 0;
          foreach ($sxl_arr as $k => $v) {
            $all += $v; 
          }
          $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
          $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
          $zhongjian = $last / 2;
          if(floor($zhongjian) == $zhongjian){
            $ckcs['data']['htiqmgzkt7'] = round(($sxl_arr[$zhongjian-0.5] + $sxl_arr[$zhongjian]) / 2,4);
          }else{
            $kss = (int)($zhongjian-0.5);
            $ckcs['data']['htiqmgzkt7'] = round($sxl_arr[$kss],4);
          }
          $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
          // 第一四分位
          $fen = $last / 4;
          if(floor($fen) == $fen){
            $yi_si = round(($sxl_arr[$fen-1] + $sxl_arr[$fen]) / 2,4);
          }else{
            $yi_si = $sxl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
          $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
          $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
          $ckcs['data']['htiqncdra2'] = round($sxl_arr[0],4); // 最小值
          $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
          $ckcs['data']['htrq306jgi'] = $value['data']['sanjhy'];
          $ckcs['data']['_rel'] = $res;
          $sxl_data = $ckcs['data'];
					$this->em->replace($ckcs,"guzhi_ckcs","add",false,false,false,false);

          // 最大值
          sort($syl_arr);
          $ckcs['data']['htiqnfqzdj'] = '市场法-市盈率';
          $ckcs['data']['htiqnfqzdj_label'] = '市场法-市盈率';
          $last = count($syl_arr);
          $ckcs['data']['htiqlsmdbc'] = round($syl_arr[$last-1],4);  // 最大值
          

          // 第三四分位
          $fen = 3 * $last / 4;
          if(floor($fen) == $fen){
            $san_si = round(($syl_arr[$fen-1] + $syl_arr[$fen]) / 2,4);
          }else{
            $san_si = $syl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
          $ckcs['data']['htiqltiooq'] = round($san_si,4);  // 第三四分位取均值
          $all = 0;
          foreach ($syl_arr as $k => $v) {
            $all += $v;
          }
          $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
          $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
          $zhongjian = $last / 2;
          if(floor($zhongjian) == $zhongjian){
            $ckcs['data']['htiqmgzkt7'] = round(($syl_arr[$zhongjian-0.5] + $syl_arr[$zhongjian]) / 2,4);
          }else{
            $kss = (int)($zhongjian-0.5);
            $ckcs['data']['htiqmgzkt7'] = round($syl_arr[$kss],4); 
          }
          $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
          // 第一四分位
          $fen = $last / 4;
          if(floor($fen) == $fen){
            $yi_si = round(($syl_arr[$fen-1] + $syl_arr[$fen]) / 2,4);
          }else{
            $yi_si = $syl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
          $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
          $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
          $ckcs['data']['htiqncdra2'] = round($syl_arr[0],4); // 最小值
          $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
          $ckcs['data']['htrq306jgi'] = $value['data']['sanjhy'];
          $ckcs['data']['_rel'] = $res;
          $syl_data = $ckcs['data'];
					$this->em->replace($ckcs,"guzhi_ckcs","add",false,false,false,false);

          // 最大值
          sort($sjl_arr);
          $ckcs['data']['htiqnfqzdj'] = '市场法-市净率';
          $ckcs['data']['htiqnfqzdj_label'] = '市场法-市净率';
          $last = count($sjl_arr);
          $ckcs['data']['htiqlsmdbc'] = round($sjl_arr[$last-1],4);  // 最大值
          // 第三四分位
          $fen = 3 * $last / 4;
          if(floor($fen) == $fen){
            $san_si = round(($sjl_arr[$fen-1] + $sjl_arr[$fen]) / 2,4);
          }else{
            $san_si = ($sjl_arr[ceil($fen)-1]);
          }
          $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
          $ckcs['data']['htiqltiooq'] = round($san_si,4); // 第三四分位取均值
          $all = 0;
          foreach ($sjl_arr as $k => $v) {
            $all += $v;
          }
          $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
          $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
          $zhongjian = $last / 2;
          if(floor($zhongjian) == $zhongjian){
            $ckcs['data']['htiqmgzkt7'] = round(($sjl_arr[$zhongjian-0.5] + $sjl_arr[$zhongjian]) / 2,4);
          }else{
            $kss = (int)($zhongjian-0.5);
            $ckcs['data']['htiqmgzkt7'] = round($sjl_arr[$kss],4); 
          }
          $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
          // 第一四分位
          $fen = $last / 4;
          if(floor($fen) == $fen){
            $yi_si = round(($sjl_arr[$fen-1] + $sjl_arr[$fen]) / 2,4);
          }else{
            $yi_si = $sjl_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
          $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
          $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
          $ckcs['data']['htiqncdra2'] = round($sjl_arr[0],4);  // 最小值
          $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
          $ckcs['data']['htrq306jgi'] = $value['data']['sanjhy'];
          $sjl_data = $ckcs['data'];
          $ckcs['data']['_rel'] = $res;
					$this->em->replace($ckcs,"guzhi_ckcs","add",false,false,false,false);

          // 最大值
          sort($ttm_arr);
          $ckcs['data']['htiqnfqzdj'] = '净资产收益率';
          $ckcs['data']['htiqnfqzdj_label'] = '净资产收益率';
          $last = count($ttm_arr);
          $ckcs['data']['htiqlsmdbc'] = round($ttm_arr[$last-1],4); // 最大值
          // 第三四分位
          $fen = 3 * $last / 4;
          if(floor($fen) == $fen){
            $san_si = round(($ttm_arr[$fen-1] + $ttm_arr[$fen]) / 2,4);
          }else{ 
            $san_si = $ttm_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqlt34py'] = round(($ckcs['data']['htiqlsmdbc'] + $san_si) / 2 ,4); // 最大值和第三四分位取均值
          $ckcs['data']['htiqltiooq'] = round($san_si,4); // 第三四分位取均值
          $all = 0;
          foreach ($ttm_arr as $k => $v) {
            $all += $v; 
          }
          $ckcs['data']['htiqlun6qb'] = round($all / $last,4); // 平均数
          $ckcs['data']['htiqltvtqr'] = round(($ckcs['data']['htiqlun6qb'] + $san_si) / 2 ,4); // 平均数和第三四分位取均值
          $zhongjian = $last / 2;
          if(floor($zhongjian) == $zhongjian){
            $ckcs['data']['htiqmgzkt7'] = round(($ttm_arr[$zhongjian-0.5] + $ttm_arr[$zhongjian]) / 2,4);
          }else{
            $kss = (int)($zhongjian-0.5);
            $ckcs['data']['htiqmgzkt7'] = round($ttm_arr[$kss],4);
          }
          $ckcs['data']['htiqluaru0'] = round(($ckcs['data']['htiqmgzkt7'] + $ckcs['data']['htiqltiooq']) / 2,4); //中位数和第三四分位取均值
          // 第一四分位
          $fen = $last / 4;
          if(floor($fen) == $fen){
            $yi_si = round(($ttm_arr[$fen-1] + $ttm_arr[$fen]) / 2,4);
          }else{
            $yi_si = $ttm_arr[ceil($fen)-1];
          }
          $ckcs['data']['htiqn5h4zx'] = round($yi_si,4); // 第一四分位
          $ckcs['data']['htiqmhnn7l'] = round(($ckcs['data']['htiqlun6qb'] + $yi_si) / 2 ,4); // 第一四分位和平均数取均值
          $ckcs['data']['htiqmi8ve8'] = round(($ckcs['data']['htiqmgzkt7'] + $yi_si) / 2 ,4); // 第一四分位和中位数取均值
          $ckcs['data']['htiqncdra2'] = round($ttm_arr[0],4); // 最小值
          $ckcs['data']['htiqn8gitk'] = round(($ckcs['data']['htiqncdra2'] + $yi_si) / 2 ,4); // 最小值和第一四分位取均值
          $ckcs['data']['htrq306jgi'] = $value['data']['sanjhy'];
          $ckcs['data']['_rel'] = $res;
					$this->em->replace($ckcs,"guzhi_ckcs","add",false,false,false,false);

          $bidui_data = array();
          if($value['data']['h1o0nyoav0'] == '市场法-市净率'){ $bidui_data = $sjl_data;}
          if($value['data']['h1o0nyoav0'] == '市场法-市盈率'){ $bidui_data = $syl_data;}
          if($value['data']['h1o0nyoav0'] == '市场法-市销率'){ $bidui_data = $sxl_data;}
          $bidui_zhi = '';
          $bidui_key = '';
          foreach ($ckcs['data'] as $k => $v) {
            if(is_numeric($v)){
              if($bidui_zhi == ''){
                $bidui_zhi = abs($arr['huig3o97c3'] - $v);
                $bidui_key = $k;
              }else{
                if(abs($arr['huig3o97c3'] - $v) < $bidui_zhi){
                  $bidui_zhi = abs($arr['huig3o97c3'] - $v);
                  $bidui_key = $k;
                }
              }
            }
          }
          $hss2gu7dv6_key = array(
            'htiqlsmdbc' => '最大值',
            'htiqlt34py' => '最大值和第三四分位取均值',
            'htiqltiooq' => '第三四分位',
            'htiqltvtqr' => '平均值和第三四分位取均值',
            'htiqluaru0' => '中位数和第三四分位取均值',
            'htiqlun6qb' => '平均值',
            'htiqmgzkt7' => '中位数',
            'htiqmhnn7l' => '第一四分位和平均值取均值',
            'htiqmi8ve8' => '第一四分位和中位数取均值',
            'htiqn5h4zx' => '第一四分位',
            'htiqn8gitk' => '最小值和第一四分位取均值', 
            'htiqncdra2' => '最小值'
          );
          $gtcl9c7k5s_pj = $bidui_data[$bidui_key];
          $info = $this->em->get_one($res);
          $info['data'] = _decode($info['data']);
          $info['data']['hss2gu7dv6'] = $hss2gu7dv6_key[$bidui_key];
          $info['data']['hss2gu7dv6_label'] = $hss2gu7dv6_key[$bidui_key];
          if($value['data']['h1o0nyoav0'] == '市场法-市净率'){ 
            $info['data']['hsn9gxm3kv'] = $gtcl9c7k5s_pj;
            $info['data']['gfpgl2jx71'] = $info['data']['gwfvy0y65t'] * $gtcl9c7k5s_pj * (1-$info['data']['hsn9lxfzrl'] / 100) *  ($info['data']['h38yx4pf9a'] / 100);
          }
          if($value['data']['h1o0nyoav0'] == '市场法-市盈率'){ 
            $info['data']['hsn9hcw256'] = $gtcl9c7k5s_pj;
            $info['data']['gfpgl2jx71'] = $info['data']['gwfvy19k7r'] * $gtcl9c7k5s_pj * (1-$info['data']['hsn9lxfzrl'] / 100) *  ($info['data']['h38yx4pf9a'] / 100);
          }
          if($value['data']['h1o0nyoav0'] == '市场法-市销率'){ 
            $info['data']['hsn9hfpyq4'] = $gtcl9c7k5s_pj;
            $info['data']['gfpgl2jx71'] = $info['data']['fsz6tqz4gz'] * $gtcl9c7k5s_pj * (1-$info['data']['hsn9lxfzrl'] / 100) *  ($info['data']['h38yx4pf9a'] / 100);
          }
          $info['data']['h8950owxca'] = round($info['gfpgl2jx71'] / $info['data']['h24b9vxbed'],4); // 未退出部分MOC
          $info['data']['h89510l4a9'] = $info['data']['gfpgl2jx71'] - $info['data']['bcgzqgyjz']; // 本次公允价值波动 
          $info['data']['h8950tefzc'] = round($info['data']['h89510l4a9'] / $info['data']['bcgzqgyjz'],6) * 100; // 本次估值波动率
          $this->em->replace($info,"xiangmuguzhi","update",false,false,false,false);
          break;
        
        default:
          # code...
          break;
      }
      if($value['data']['h1o0nyoav0'] == '收益法-现金流折现法'){
				$data = $this->em->get_one($res);
				$data['data'] = _decode($data['data']);
				$data['data']['gfpgl2jx71'] = 0;
				$gzfftzmx_gk = $this->em->get("AND `type` = 'gzfftzmx_gk' AND `del` = 0 AND `data`->>'$.name' = '{$value['uuid']}'");
				$guzhi_qyzsyl_n =array(
					'0.00' => 'htjkye8wbn',
					'0.08' => 'htjkyenwk8',
					'0.25' => 'htjkyf9kwr',
					'0.50' => 'htjkyfocqp',
					'0.75' => 'htjkyg22jh',
					'1.00' => 'htjkyge9l2',
					'2.00' => 'htjkygr5ci',
					'3.00' => 'htjkyh2ygb',
					'4.00' => 'htjkyhfcnm',
					'5.00' => 'htjkyhqml4',
					'6.00' => 'htjl0tpben',
					'7.00' => 'htjl0u6hu7',
					'8.00' => 'htjl0ujxec',
					'9.00' => 'htjl0uxd31',
					'10.00' => 'htjl0v9gdh',
					'15.00' => 'htjl0vmgci',
					'20.00' => 'htjl0w0c2w',
					'30.00' => 'htjl0wcc59'
				);
        $gzfftzmx_arr = array();
        foreach ($gzfftzmx_gk as $k => $v) {
					$v['data'] = _decode($v['data']);
					$gzfftzmx['type'] = 'gzfftzmx';
					$gzfftzmx['data'] = $v['data'];
					$gzfftzmx['data']['h2v8g9k39a'] = $jizhunri;
          $gzfftzmx['data']['htir2jb73f'] = 100;
					// 计息天数(年） h2v8gb12z6 ：（ 回购计息截止日 h8ev03erwb — 回购起息日 h8ev02mw41）/365
					$gzfftzmx['data']['h2v8gb12z6'] = round((strtotime($v['data']['h8ev03erwb']) - strtotime($v['data']['h8ev02mw41'])) / 86400 / 365 ,4); 
					// 应付利息（复利） hthj6sqxa4= 计息本金 *（1+回购利率  ）的n次方-计息本金ggpec8xi57
					$gzfftzmx['data']['hthj6sqxa4'] = round($v['data']['ggpec8xi57'] * pow(1 + ($v['data']['h2cmti4smx'] / 100),$gzfftzmx['data']['h2v8gb12z6']) - $v['data']['ggpec8xi57'],4);
					// 应付利息(单利） hthj6iehyr=计息本金*计息天数(年）*回购利率
					$gzfftzmx['data']['hthj6iehyr'] = $v['data']['ggpec8xi57'] * $gzfftzmx['data']['h2v8gb12z6'] * ($v['data']['h2cmti4smx'] / 100);
					// 预计回款本金折现年限=(预计回款日期-估值基准日）/365
					$gzfftzmx['data']['hthjosfmh1'] = round((strtotime($v['data']['htcshzviba']) - strtotime($jizhunri)) / 86400 / 365,4);
					// 预计回款利息折现年限=(预计利息回款日期-估值基准日）/365
					$gzfftzmx['data']['hthjoypl7f'] = round((strtotime($v['data']['htcsi59133']) - strtotime($jizhunri)) / 86400 / 365,4);
		
					$guzhi_qyzsyl = $this->em->get("AND `type` = 'guzhi_qyzsyl' AND `del` = 0 AND `data`->>'$.hthml1py5n' = '{$v['data']['hthml1py5n']}' AND `data`->>'$.htjlkwbanp' = '{$jizhunri}'")[0];
					$guzhi_qyzsyl['data'] = _decode($guzhi_qyzsyl['data']);
					// 本金折现率 h2v8h490xl
					$benjin_nianhua = round((strtotime($v['data']['htcshzviba']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
					
					$gzfftzmx['data']['h2v8h490xl'] = 0;
					if(in_array($benjin_nianhua,array_keys($guzhi_qyzsyl_n))){
						$zxl = $guzhi_qyzsyl_n[$benjin_nianhua];
					}else{
						$min = 0;
						$max = 0;
						foreach (array_keys($guzhi_qyzsyl_n) as $q => $w) {
							if($w < $benjin_nianhua){
								$min = $w;
							}else{
								$max = $w;
								break;
							}
							
						}
						$min_val = $guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$min]]; 
						$max_val = $guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$max]];
					
 						$gzfftzmx['data']['h2v8h490xl'] = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($benjin_nianhua - $min) + $min_val,4);
						
					}
					// 本金公允价值=计息本金/(1+本金折现率）的预计回款本金折现年限次方*综合折扣率
					$gzfftzmx['data']['hthjp807fg'] = round($v['data']['ggpec8xi57'] / pow((1 + $gzfftzmx['data']['h2v8h490xl'] / 100),$benjin_nianhua),4);
					// 利息折现率 hthiq35o6a	
					$lixi_nianhua = round((strtotime($v['data']['htcsi59133']) - strtotime($jizhunri)) / 86400 / 365 ,4); 
					$gzfftzmx['data']['hthiq35o6a'] = 0;
					if(in_array($lixi_nianhua,array_keys($guzhi_qyzsyl_n))){
						$zxl = $guzhi_qyzsyl_n[$lixi_nianhua];
					}else{
						$min = 0;
						$max = 0;
						foreach (array_keys($guzhi_qyzsyl_n) as $q => $w) {
							if($w < $lixi_nianhua){
								$min = $w;
							}else{
								$max = $w;
								break;
							}
							
						}
						$min_val =$guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$min]];         
						$max_val =$guzhi_qyzsyl['data'][$guzhi_qyzsyl_n[$max]];
						$gzfftzmx['data']['hthiq35o6a'] = round(round(round(($max_val - $min_val),4) / ($max - $min),4) * ($lixi_nianhua - $min) + $min_val,4);
						
					}
					if($v['data']['h2v8g8wqg7'] == '单利'){  
						$gzfftzmx['data']['hthjqfasmc'] = round($gzfftzmx['data']['hthj6iehyr'] / pow((1 + $gzfftzmx['data']['hthiq35o6a'] / 100),$lixi_nianhua),4);
					}
					if($v['data']['h2v8g8wqg7'] == '复利'){
						$gzfftzmx['data']['hthjqfasmc'] = round($gzfftzmx['data']['hthj6sqxa4'] / pow((1 + $gzfftzmx['data']['hthiq35o6a'] / 100),$lixi_nianhua),4);
            
					}
          if(is_infinite($gzfftzmx['data']['hthjqfasmc']) )$gzfftzmx['data']['hthjqfasmc']=0;
          if(is_infinite($gzfftzmx['data']['hthjp807fg']) )$gzfftzmx['data']['hthjp807fg']=0;
					$gzfftzmx['data']['h2v8hab2h5'] = $gzfftzmx['data']['hthjqfasmc'] + $gzfftzmx['data']['hthjp807fg'];
          if($v['data']['htcs9dpff1'] == '本金利息未收回'){
						$data['data']['gfpgl2jx71'] += $gzfftzmx['data']['h2v8hab2h5']; 
					}
          if($v['data']['htcs9dpff1'] == '本金已收回(利息未收回)'){
						$data['data']['gfpgl2jx71'] += $gzfftzmx['data']['h2v8hab2h5']; 
					}
          if($v['data']['htcs9dpff1'] == '利息已收回(本金未收回)'){
						$data['data']['gfpgl2jx71'] += $gzfftzmx['data']['h2v8hab2h5']; 
					}
					$gzfftzmx['data']['_rel'] = $res;
          $gzfftzmx['sid'] = WEBSID;
          $gzfftzmx_arr[] = $gzfftzmx;
					// $this->em->replace($gzfftzmx,"gzfftzmx","add",false,false,false,false);
				}
        $this->em->bunch_add($gzfftzmx_arr);
				$data['data']['h8950owxca'] = round($data['data']['gfpgl2jx71'] / $data['data']['h24b9vxbed'],4); // 未 退出部分MOC
				$data['data']['h89510l4a9'] = $data['data']['gfpgl2jx71'] - $data['data']['bcgzqgyjz']; // 本次公允d值波动 
				$data['data']['h8950tefzc'] = round($data['data']['h89510l4a9'] / $data['data']['bcgzqgyjz'],6) * 100; // 本次估值波动率
				$data['data']['uuid'] = $res;
				$res = $this->em->replace($data,"xiangmuguzhi","update",false,false,false,false);
      }
    }
    
  }

  /**
   * @taskSN      2024-19926
   * @version     release/v7.1
   * @author      秦佳惠 <jhqin@pepm.com.cn>
   * @taskFlow    https://oa.vc800.com/?/flow/view/hwrgq5iy6s
   * @description 根据现金流自动生成回购明细信息模块信息
   * @param       String $eid 现金流数据uuid
   * @param       String $is_del 是否删除数据
   */
  function add_or_update_gzfftzmx($eid, $is_del = ''){
    $em = $this->em;
    $sid = WEBSID;
    $cashflow_portfolio = $em->get_one($eid);
    $cashflow_portfolio['data'] = _decode($cashflow_portfolio['data']);
    $company_uuid = $cashflow_portfolio['data']['company'];
    $cf_type = $cashflow_portfolio['data']['cf_type'];
    
    if(!in_array($cf_type, ['(流出)投资款', '(流入)退出款']))return;
    // (流出)投资款只有一条
    $add_cashflow_portfolio = $em->get("AND `type` = 'cashflow_portfolio' AND `del` = 0 AND `data`->>'$.company' = '$company_uuid' AND `data`->>'$.cf_type' IN ('(流出)投资款','(流入)退出款') ORDER BY `data`->>'$.fukuanshijian' ASC");
    foreach($add_cashflow_portfolio as $value){
      $value['data'] = _decode($value['data']);
      if($value['data']['cf_type'] == '(流出)投资款'){
        $is_true = true;
        $invest += $value['data']['fukuanjine'];
        // 日期 
        if(empty($date))$date = $value['data']['fukuanshijian'];
      }
      if($value['data']['cf_type'] == '(流入)退出款')$quit += $value['data']['h117obgb3r'];
    }
    
    $company = $em->get_one($company_uuid);
    $company['data'] = _decode($company['data']);
    
    if(!$is_true){
      $sql = "UPDATE `entity` SET `del` = 1 WHERE `sid` = '$sid' AND `type` = 'gzfftzmx_gk' AND `del` = 0 AND `data`->>'$.name' = '$company_uuid' AND `data`->>'$.htcs9dpff1' = '本金利息未收回'";
      $em->db->query($sql);
    }else{
      // 本金利息未收回
      $uncalled_item = $em->get("AND `type` = 'gzfftzmx_gk' AND `del` = 0 AND `data`->>'$.name' = '$company_uuid' AND `data`->>'$.htcs9dpff1' = '本金利息未收回'")[0];

      $new_item = empty($uncalled_item) ? ['uuid' => uuid(), 'type' => 'gzfftzmx_gk'] : $uncalled_item;
      $new_item['data'] = _decode($new_item['data']);

      $new_item['data']['name'] = $cashflow_portfolio['data']['company'];
      $new_item['data']['name_label'] = $cashflow_portfolio['data']['company_label'];

      $new_item['data']['hthml1py5n'] = $company['data']['hthml1py5n'];
      $new_item['data']['hthml1py5n_label'] = $company['data']['hthml1py5n_label'];
      // 计息本金 ggpec8xi57
      $new_item['data']['ggpec8xi57'] = $invest - $quit;
      // 投资日期 gi1xsxg1cf  回购起息日 h8ev02mw41
      $new_item['data']['gi1xsxg1cf'] = $new_item['data']['h8ev02mw41'] = $date;
      // 收回情况 htcs9dpff1
      $new_item['data']['htcs9dpff1'] = $new_item['data']['htcs9dpff1_label'] = "本金利息未收回";
      // 利率形式 h2v8g8wqg7
      $new_item['data']['h2v8g8wqg7'] = $new_item['data']['h2v8g8wqg7_label'] = "单利";
      $em->replace($new_item,$new_item['type'],'update',false,false,false,false);
    
    }
    
    if($cf_type == '(流入)退出款' && $is_del == 'del'){
      $sql = "UPDATE `entity` SET `del` = 1 WHERE `sid` = '$sid' AND `type` = 'gzfftzmx_gk' AND `del` = 0 AND `_rel` = '$eid'";
      $em->db->query($sql);
      return;
    }
    
    if($cf_type == '(流入)退出款'){
      $gzfftzmx = $em->get("AND `type` = 'gzfftzmx_gk' AND `del` = 0 AND `_rel` = '$eid'")[0];
      $item = empty($gzfftzmx) ? ['uuid' => uuid(), 'type' => 'gzfftzmx_gk'] : $gzfftzmx;
      $item['data'] = _decode($item['data']);

      $item['data']['_rel'] = $eid;
      $item['data']['name'] = $cashflow_portfolio['data']['company'];
      $item['data']['name_label'] = $cashflow_portfolio['data']['company_label'];
      $item['data']['hthml1py5n'] = $company['data']['hthml1py5n'];
      $item['data']['hthml1py5n_label'] = $company['data']['hthml1py5n_label'];
      // 回购计息截止日 h8ev03erwb
      $item['data']['h8ev03erwb'] = $cashflow_portfolio['data']['fukuanshijian'];
      // 投资日期 gi1xsxg1cf  回购起息日 h8ev02mw41
      $item['data']['gi1xsxg1cf'] = $item['data']['h8ev02mw41'] = $date;
      // 利率形式 h2v8g8wqg7
      $item['data']['h2v8g8wqg7'] = $item['data']['h2v8g8wqg7_label'] = "单利";
      
      if($cashflow_portfolio['data']['h117obgb3r'] != 0 && $cashflow_portfolio['data']['h117ocajr7'] == 0){
        $ggpec8xi57 = $cashflow_portfolio['data']['h117obgb3r'];
        $htcs9dpff1 = "本金已收回(利息未收回)";
      }
      if($cashflow_portfolio['data']['h117obgb3r'] == 0 && $cashflow_portfolio['data']['h117ocajr7'] != 0){
        $ggpec8xi57 = 0;
        $htcs9dpff1 = "利息已收回(本金未收回)";
      }
      if($cashflow_portfolio['data']['h117obgb3r'] != 0 && $cashflow_portfolio['data']['h117ocajr7'] != 0){
        $ggpec8xi57 = $cashflow_portfolio['data']['h117obgb3r'];
        $htcs9dpff1 = "本金利息已收回";
      }
      // 计息本金 ggpec8xi57
      $item['data']['ggpec8xi57'] = $ggpec8xi57;
      // 收回情况 htcs9dpff1
      $item['data']['htcs9dpff1'] = $item['data']['htcs9dpff1_label'] = $htcs9dpff1;
      $em->replace($item,$item['type'],'update',false,false,false,false);
    }
  }
  
  function once_history_cashflow(){
    $em = $this->em;
    $all_cashflow = $em->get("AND `type` = 'cashflow_portfolio' AND `del` = 0 AND `data`->>'$.company' != '' AND `data`->>'$.cf_type' IN ('(流出)投资款','(流入)退出款') ORDER BY `data`->>'$.fukuanshijian' ASC",0,9999);
    array_walk($all_cashflow, function(&$item) use (&$company_box, &$tc_box) {
      $item['data'] = _decode($item['data']);
      $company_uuid = $item['data']['company'];
      $cf_type = $item['data']['cf_type'];
      
      if($cf_type == '(流出)投资款'){
        $company_box[$company_uuid]['is_true'] = true;
        $company_box[$company_uuid]['invest'] += $item['data']['fukuanjine'];
        // 日期 
        if(empty($company_box[$company_uuid]['date']))$company_box[$company_uuid]['date'] = $item['data']['fukuanshijian'];
      }
      if($cf_type == '(流入)退出款'){
        $tc_box[$company_uuid][] = $item;
        $company_box[$company_uuid]['quit'] += $item['data']['h117obgb3r'];
      }
    });
    
    $all_company = $em->get("AND `type` = 'company' AND `del` = 0", 0, 9999);
    foreach($all_company as $value){
      $uuid = $value['uuid'];
      $value['data'] = _decode($value['data']);
      if(!$company_box[$uuid]['is_true'] && count($tc_box[$uuid]) == 0)continue;
      $new_item = [];
      
      $new_item['type'] = "gzfftzmx_gk";
      $new_item['data']['name'] = $uuid;
      $new_item['data']['name_label'] = $value['data']['name'];

      $new_item['data']['hthml1py5n'] = $value['data']['hthml1py5n'];
      $new_item['data']['hthml1py5n_label'] = $value['data']['hthml1py5n_label'];
      // 利率形式 h2v8g8wqg7
      $new_item['data']['h2v8g8wqg7'] = $new_item['data']['h2v8g8wqg7_label'] = "单利";
      // 投资日期 gi1xsxg1cf  回购起息日 h8ev02mw41
      $new_item['data']['gi1xsxg1cf'] = $new_item['data']['h8ev02mw41'] = $company_box[$uuid]['date'];
      
      if($company_box[$uuid]['is_true']){
        $new_item['uuid'] = uuid();
        // 计息本金 ggpec8xi57
        $new_item['data']['ggpec8xi57'] = $company_box[$uuid]['invest'] - $company_box[$uuid]['quit'];
        // 收回情况 htcs9dpff1
        $new_item['data']['htcs9dpff1'] = $new_item['data']['htcs9dpff1_label'] = "本金利息未收回";
        $em->replace($new_item,$new_item['type'],'update',false,false,false,false);
      }
      if(count($tc_box[$uuid]) == 0)continue;
      
      foreach($tc_box[$uuid] as $val){
        $item = $new_item;
        $item['uuid'] = uuid();
        $item['data']['_rel'] = $val['uuid'];
        // 回购计息截止日 h8ev03erwb
        $item['data']['h8ev03erwb'] = $val['data']['fukuanshijian'];
        if($val['data']['h117obgb3r'] != 0 && $val['data']['h117ocajr7'] == 0){
          $ggpec8xi57 = $val['data']['h117obgb3r'];
          $htcs9dpff1 = "本金已收回(利息未收回)";
        }
        if($val['data']['h117obgb3r'] == 0 && $val['data']['h117ocajr7'] != 0){
          $ggpec8xi57 = 0;
          $htcs9dpff1 = "利息已收回(本金未收回)";
        }
        if($val['data']['h117obgb3r'] != 0 && $val['data']['h117ocajr7'] != 0){
          $ggpec8xi57 = $val['data']['h117obgb3r'];
          $htcs9dpff1 = "本金利息已收回";
        }
        // 计息本金 ggpec8xi57
        $item['data']['ggpec8xi57'] = $ggpec8xi57;
        // 收回情况 htcs9dpff1
        $item['data']['htcs9dpff1'] = $item['data']['htcs9dpff1_label'] = $htcs9dpff1;
        $em->replace($item,$item['type'],'update',false,false,false,false);
      }
    }
  }
  
}   // end of class
