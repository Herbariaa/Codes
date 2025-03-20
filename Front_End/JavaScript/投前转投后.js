var JSONObject = Java.type('com.alibaba.fastjson.JSON');
var contextValue = {"appId":"investment","tenantId":"62848890c99be63831e2df00","userId":"6285ebe7c99be63831e3af9b"};
function toJsonStr(jsonObj){
    // 传入用户信息
    // jsonObj.context=eval('(' + JSONObject.toJSONString(context) + ')');
    jsonObj.context = contextValue;
    var str=JSON.stringify(jsonObj); 
    //log.info(JSONObject.parseObject(str));
    return JSONObject.parseObject(str);
}

//0、初始化及投前素材准备
var apiNameBefore   = "Object_41ix__u";
var apiNameAfter    = "project";
var ROUND_MAP = {
    "10": "种子轮", "20": "天使轮", "30": "Pre-A轮", "40": "A轮", "50": "A+轮", 
    "60": "Pre-B轮", "70": "B轮", "80": "B+轮", "90": "Pre-C轮", "100": "C轮", 
    "110": "C+轮", "120": "D轮及以后", "130": "Pre-IPO", "140": "IPO", "150": "其他", 
}; // 轮次映射

// var dataId    = "63186c909e1bae6769529ecd";
//var dataIdBefore    = "63186c909e1bae6769529ecd";//dataId;
var dataIdBefore = dataId;
log.info('投前项目ID: ' + dataIdBefore);
//通过ID查询投前项目情况
var parArg ={"objectDescribeApiName":apiNameBefore,"idList":[dataIdBefore]} ; 
var resultBefore = jsBusiness.metaData_queryByIdList(toJsonStr(parArg)).result[0].map;

//var dataIdAfter     = "63e0a86c9e1bae35787929b4"; //TODO1:本内容需要定位查找
var dataIdAfter           = resultBefore.Field_4s7t__u; //相关投后项目ID
log.info('投后项目ID: ' + dataIdAfter);

{//1、确认本轮所有本轮投资协议 Object_05cs__u  

    //输出：本轮所有投资协议 resultTZXY
    var apiNameTZXY     = "Object_05cs__u";
    var parArg ={
        "describeApiName":apiNameTZXY,
        "searchTemplateQuery":{"filters":[{
            "fieldName": "Field_it9x__u", "operator": "EQ", "fieldValues": [dataIdBefore]
        }]//投前项目 Field_it9x__u    
    }}; 
    var resultTZXY = jsBusiness.metaData_queryByCondition(toJsonStr(parArg)).result;
    log.info("关联的投资协议共 " + resultTZXY.length + ' 条');
    log.info(resultTZXY[0].map.id);//若无则程序在此终止
    
    //2、新建项目权益变更，并返回Id；同步复制上一轮的fcec作为本轮初步情况
    //输出 本轮项目权益变更 qybgId
    log.info('开始创建权益变更');
    var createMap = {
        "object_describe_api_name":"V2_n0l9__u",
        "record_type":"default__u",
        "Field_spry__u": dataIdAfter,
    };
    createMap["V2_zgb__u"] = resultBefore.V2_blthzgb__u;//本轮投后总股本
    createMap["V2_xmzxgz__u"] = resultBefore.V2_thgz__u;//本轮投后估值
    createMap["subject__u"] = ROUND_MAP[resultBefore.Field_rdob__u]; //融资轮次
    var parArg = {"objectData":{"map":createMap}};
    var resultn0l9 = jsBusiness.metaData_createDataAndAuth(toJsonStr(parArg));
    log.info(resultn0l9);
    var qybgId   = resultn0l9.result;//新增一轮权益变更 qybgId
    log.info('2权益变更ID: '+ qybgId);

    //找到该项目下上一次权益变更内容
    var parArg ={
        "describeApiName":"V2_n0l9__u",
        "searchTemplateQuery":{
            "filters":[{
                "fieldName": "Field_spry__u", "operator": "EQ", "fieldValues": [dataIdAfter]
            }],
            "orders":[{"fieldName": "created_at", "isAsc": false}],//创建时间由近及远
    }}; 
    var result = jsBusiness.metaData_queryByCondition(toJsonStr(parArg)).result;
    log.info('权益变更历史记录共: ' + result.length + '条');
    
    if (result ===null || result.length < 2) { //没有历史记录，本阶段不动
        log.info("2-1 没有历史记录，本阶段不动");
    } else {//复制转化上一轮记录进入本轮
        log.info("2-2复制转化上一轮记录进入本轮");
        var latestId  = result[1].id;//最近一条权益变更记录Id
        log.info(latestId);

        //2、利用上一轮的fcec初始化本轮的fcec
        var parArg ={
            "describeApiName":"V2_fcec__u",
            "searchTemplateQuery":{
                "filters":[{
                    "fieldName": "Field_ny27__u", "operator": "EQ", "fieldValues": [latestId]//上一轮
                }]
            }
        };
        var resultFcec = jsBusiness.metaData_queryByCondition(toJsonStr(parArg)).result;
        log.info('此前的股东权益共: ' + resultFcec.length + '条');

        if(resultFcec === null || resultFcec.length ===0){
            log.info('此前无股东权益');
        }
        else{
            // log.info(resultFcec.length);
            
            for(var i=0; i<resultFcec.length;i++){
                //创建实体数据
                var parArg ={"objectData":{"map":{
                    "object_describe_api_name":"V2_fcec__u",
                    "record_type":"default__u",
                    "Field_jr0b__u":resultFcec[i].map.Field_jr0b__u,//主体权益详情
                    "Field_ny27__u":qybgId,//项目权益变更
                    "V2_lscygb__u": Number(resultFcec[i].map.V2_lgzr__u)+Number(resultFcec[i].map.V2_xzgb__u)+Number(resultFcec[i].map.V2_lscygb__u)
                }}};
                var resultM = jsBusiness.metaData_createDataAndAuth(toJsonStr(parArg));
                var resultMid = resultM.result;
                log.info('创建股东权益: ' + resultMid + '完成');
            }
        }
    }
    log.info('2 权益及变更完成');
}

{
    //3、根据投资协议，创建主体权益详情 V2_3bw8，并更新对应fcec
    log.info('3. 创建主体权益详情并更新股本权益');
    log.info('共有 ' + resultTZXY.length + ' 条投资协议');
    
    for(var i=0; i<resultTZXY.length; i++) { //轮询投资协议

        //主体权益详情
        var projectId = dataIdAfter;
        var fundId    = resultTZXY[i].map.Field_j0pz__u; // 出资基金
        var baseDate = resultTZXY[i].map.Field_ok6w__u; // 签约日期
        log.info('基金ID: ' + fundId);
        //查询主体详情，若无则新建
        var parArg ={
            "describeApiName":"V2_3bw8__u",
            "searchTemplateQuery":{
                "filters":[{
                    "fieldName": "Field_ju7z__u", "operator": "EQ", "fieldValues": [dataIdAfter] // 项目
                }, {
                    "fieldName": "Field_r56p__u", "operator": "EQ", "fieldValues": [fundId] // 基金
                }]
            }
        }; 
        var resultZTQY = jsBusiness.metaData_queryByCondition(toJsonStr(parArg)).result;//条件查询相应主体权益
        log.info('符合条件的主体权益详情有: ' + resultZTQY.length + ' 条');
        var qyxqId;
        
        if(resultZTQY === null || resultZTQY.length ===0){
            log.info('该基金名下无权益详情，创建之');
            var map = {
                "object_describe_api_name":"V2_3bw8__u",
                "record_type":"default__u",
                "Field_ju7z__u":projectId,
                "Field_r56p__u":fundId,
                "date__u": baseDate
            };
            var parArg     = {"objectData":{"map":map}};
            var result3bw8 = jsBusiness.metaData_createDataAndAuth(toJsonStr(parArg));
            qyxqId         = result3bw8.result;
            //log.info(['3、新建',qyxqId]);
        }
        else{
            qyxqId         = resultZTQY[0].id;
            log.info('3、已有' + qyxqId );
        }

        //查询是否有已有历史转移过来的股本fcec
        var parArg ={"describeApiName":"V2_fcec__u",
            "searchTemplateQuery":{"filters":[
                {"fieldName": "Field_jr0b__u", "operator": "EQ", "fieldValues": [qyxqId]},//本主体
                {"fieldName": "Field_ny27__u", "operator": "EQ", "fieldValues": [qybgId]}//本轮
            ]}};
        var resultFcec2 = jsBusiness.metaData_queryByCondition(toJsonStr(parArg)).result;
        if(resultFcec2 === null || resultFcec2.length ===0){//历史上无此记录，则新建
            //新建对应的股本权益
            var map = {
                "object_describe_api_name": "V2_fcec__u",
                "record_type":              "default__u",
                "V2_lgzr__u":               resultTZXY[i].map.V2_lgzr__u,
                "V2_xzgb__u":               resultTZXY[i].map.V2_xzgb__u,
                "Field_jr0b__u":            qyxqId,//主体权益详情
                "Field_ny27__u":            qybgId,//项目权益变更
            };
            var parArg ={"objectData":{"map":map}};
            var resultmid = jsBusiness.metaData_createDataAndAuth(toJsonStr(parArg));
            var gbqyId     = resultmid.result;

        }
        else{//有，则更新部分内容（新增股本、老股转让）
            var parArg = {"describeApiName":"V2_fcec__u",
                            "fieldApiNames":["V2_lgzr__u","V2_xzgb__u"],
                            "objectDataList":[{"map":{"id":resultFcec2[0].id,"V2_lgzr__u":resultTZXY[i].map.V2_lgzr__u,"V2_xzgb__u":resultTZXY[i].map.V2_xzgb__u}}]}; 
            var resultmid = jsBusiness.metaData_update(toJsonStr(parArg));
            var gbqyId = resultFcec2[0].id;
        }
        

        
        //投资协议绑定主体权益详情 3bw8
        var parArg = {"describeApiName":"Object_05cs__u",
                "fieldApiNames":["Field_91qw__u"],"objectDataList":[{"map":{"id":resultTZXY[i].id,"Field_91qw__u":qyxqId}}]}; 
        var result = jsBusiness.metaData_update(toJsonStr(parArg));
    }
}
