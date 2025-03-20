var JSONObject = Java.type('com.alibaba.fastjson.JSON');
 function toJsonStr(jsonObj){
     //传入用户信息
     jsonObj.context=eval('(' + JSONObject.toJSONString(context) + ')');
     var str=JSON.stringify(jsonObj); 
     log.info(JSONObject.parseObject(str));
     return JSONObject.parseObject(str);
}

//通过ID查询实体数据 
 var parArg ={"objectDescribeApiName":"Object_ai0m__u","idList":[dataId]} ; 
 var result = jsBusiness.metaData_queryByIdList(toJsonStr(parArg)).result[0].map;


var xmName = result.name;
var projectID = result.id;
var fuzeren = result.Field_zxj0__u;
var jingbanren =  result.owner_id;
var md =  result.Field_pf6t__u;
 
 
 //创建实体数据
        var cparArg ={"objectData":{"map":{
            "object_describe_api_name":"Fengkong__u",
            "record_type":"default__u",
            "project__u":projectID
        }}};
 
 
 var cresult = jsBusiness.metaData_create(toJsonStr(cparArg));
 var JKId = JSON.parse(cresult).result.id;

//绑一条risk在上面
var fxName1 = xmName+ "项目"+"-承诺上市申报时间提前90天";
var fxName2 = xmName+ "项目"+"-承诺上市申报时间提前180天";
var fxName3 = xmName+ "项目"+"-承诺上市完成时间提前90天";
var fxName4 = xmName+ "项目"+"-承诺上市完成时间提前180天";
var fxName5 = xmName+ "项目"+"-约定回购触发日期提前90天";
var fxName6 = xmName+ "项目"+"-约定回购触发日期提前180天";


var message1= xmName+ "项目"+" 距离承诺上市申报时间仅有90天，请知悉";
var message2= xmName+ "项目"+" 距离承诺上市申报时间仅有180天，请知悉";
var message3= xmName+ "项目"+" 距离承诺上市完成时间仅有90天，请知悉";
var message4= xmName+ "项目"+" 距离承诺上市完成时间仅有180天，请知悉";
var message5= xmName+ "项目"+" 距离约定回购触发日期仅有90天，请知悉";
var message6= xmName+ "项目"+" 距离约定回购触发日期仅有180天，请知悉";

var recipient = ["63bb99894f6be9120ba6b99f","605bf34cc05e846d18860ddc","605bf34cc05e846d18860ddb","605bf34cc05e846d18860dd9","605bf34cc05e846d18860dcb"];//吴双，蔡晓君,陈妍，严娜，程放

if(fuzeren){
    for(i=0;i<fuzeren.length;i++){
        recipient.push(fuzeren[i]);
    }
}
if(jingbanren){
    for(i=0;i<jingbanren.length;i++){
        recipient.push(jingbanren[i]);
    }
}
if(md){
    for(i=0;i<md.length;i++){
        recipient.push(md[i]);
    }
}

var xxx = [
    {"name":fxName1,
        "condition":"[{\"fieldName\":\"Field_wyc7__u\",\"fieldValues\":[\"90\"],\"operator\":\"EQ\",\"subFieldName\":\"equivalentMoney\"}]",
       "message":message1,
       "pattern":" (1) "}, 
    {"name":fxName2,
        "condition":"[{\"fieldName\":\"Field_wyc7__u\",\"fieldValues\":[\"180\"],\"operator\":\"EQ\",\"subFieldName\":\"equivalentMoney\"}]",
       "message":message2,
       "pattern":" (1) "},   
    {"name":fxName3,
        "condition":"[{\"fieldName\":\"cha3__u\",\"fieldValues\":[\"90\"],\"operator\":\"EQ\",\"subFieldName\":\"equivalentMoney\"}]",
       "message":message3,
       "pattern":" (1) "}, 
    {"name":fxName4,
        "condition":"[{\"fieldName\":\"cha3__u\",\"fieldValues\":[\"180\"],\"operator\":\"EQ\",\"subFieldName\":\"equivalentMoney\"}]",
       "message":message4,
       "pattern":" (1) "},     
    {"name":fxName5,
        "condition":"[{\"fieldName\":\"Field_c1qe__u\",\"fieldValues\":[\"90\"],\"operator\":\"EQ\",\"subFieldName\":\"equivalentMoney\"}]",
       "message":message5,
       "pattern":" (1) "},
    {"name":fxName6,
        "condition":"[{\"fieldName\":\"Field_c1qe__u\",\"fieldValues\":[\"180\"],\"operator\":\"EQ\",\"subFieldName\":\"equivalentMoney\"}]",
       "message":message6,
       "pattern":" (1) "},
       ];
for(i=0;i<xxx.length;i++)
 {
   
 var parArg ={
     "objectData":{
        "map":{
            "object_describe_api_name":"risk",
            "record_type":"business_risk",
            "name":xxx[i].name,
            "recipient":recipient,
            "business_stage":null,
            "is_open":true,
            "is_multi_open":false,
            "object_name":"Fengkong__u",
            "data_name":JKId,
            "is_send":true,
            "condition": xxx[i].condition,
            "send_mode":[
                "message"
            ],
            "message":xxx[i].message,
            "pattern":xxx[i].pattern,
            "status":"monitoring"
        }
    }
}
//创建实体数据

 var result = jsBusiness.metaData_create(toJsonStr(parArg));
 
  }