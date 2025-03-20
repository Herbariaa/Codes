//定义用户信息
var contextValue={"appId":"investment","tenantId":"605bee33c05e846d18860b11","userId":"admin"};
//初始化，定时任务
var JSONObject = Java.type('com.alibaba.fastjson.JSON');
function toJsonStr(jsonObj){
    //传入用户信息
    jsonObj.context = contextValue;
    var str=JSON.stringify(jsonObj); 
  //  log.info(JSONObject.parseObject(str));
    return JSONObject.parseObject(str);
}
//以上初始化已经更改
//通过ID查询实体数据 
//var apiName = "Fengkong__u";
//var dataId = "61bb0068c8be2206b572ae7f";
//var parArg ={"objectDescribeApiName":apiName,"idList":[dataId]} ; 
//var result = jsBusiness.metaData_queryByIdList(toJsonStr(parArg)).result[0].map;


// 获取今天的时间戳
var today = new Date();
var todaystart = new Date(today.getFullYear(),today.getMonth(),today.getDate())
var todaytime = todaystart.getTime();




// 遍历“项目风控条款时间节点”实体下的所有数据
var queryJSON ={"describeApiName":"Fengkong__u","searchTemplateQuery":{"filters":[]}}; 
var getOffList = jsBusiness.metaData_queryByCondition(toJsonStr(queryJSON)).result;
//都已经找出来了

for (var i = 0; i < getOffList.length; i++) {
    var item = getOffList[i].map;
    var offId = item.id;

var huigoudate = item.huigou__u;
var shenbaodate = item.shenbao__u;
var wanchengdate = item.wancheng__u;

var a = Number(huigoudate)-Number(todaytime);
var b = Number(shenbaodate)-Number(todaytime);
var c = Number(wanchengdate)-Number(todaytime);   

 //修改实体数据 
var parArg = {"describeApiName":"Fengkong__u","fieldApiNames":
["today__u",
"Field_c1qe__u",
"Field_wyc7__u",
"cha3__u"],
"objectDataList":[{"map":{"id":offId,
"today__u":todaytime,
"Field_c1qe__u":a/3600/1000/24,
"Field_wyc7__u":b/3600/1000/24,
"cha3__u":c/3600/1000/24
}}]}; 
 var result = jsBusiness.metaData_update(toJsonStr(parArg));

}
