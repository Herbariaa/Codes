<style>
    .heat_map_of_fund_table .table-container {
    width: 100%;
    overflow-x: auto;
    display: block;
}

.heat_map_of_fund_table table {
    width: max-content;
    min-width: 100%;
    display: inline-block;
}

.heat_map_of_fund_table table.dataTable tbody td:first-child,.heat_map_of_fund_table td {
    text-align: right !important;
}

.heat_map_of_fund_table .row-weight td {
    font-weight: bold !important;
    //font-size: 18px;
}

.heat_map_of_fund_table th {
    text-align: center !important;
    font-weight: bold !important;
}

.heat_map_of_fund_table th.fixed-column,
  .heat_map_of_fund_table td.fixed-column {
    left: 0;
    position: sticky;
    background-color: #f9f9f9;
    z-index: 1;
    font-weight: bold !important;
}

.heat_map_of_fund_table .layui-table {
    background-color: var(--background-color);
}

.heat_map_of_fund_chart .echarts_body {
    display: flex;
    flex-direction: row;
    justify-content: space-around;
    text-align: center;
    width: 100%;
}

.heat_map_of_fund_chart .echarts_body .tuli_box {
    display: flex;
    flex-direction: row;
    width: 50%;
    justify-content: flex-start;
}

.heat_map_of_fund_chart .tuli_box_p {
    display: flex;
    flex-direction: row;
    width: 100%;
}

.heat_map_of_fund_chart .echarts_body .tuli_box #project_amount3,.heat_map_of_fund_chart .echarts_body .tuli_box #number_of_items3 {
    width: 100%;
    height: 500px;
}

.heat_map_of_fund_chart .tuli_box_p p {
    text-align: center;
    padding-bottom: 10px;
    font-size: 18px;
    font-weight: 700;
    width: 50%;
}
</style>
<style>
    .heat_map_of_type .layui-form-select {
    display: none;
}

.heat_map_of_type.pepm-table,.heat_map_of_type .pepm-table {
    width: 50%;
    float: right;
    background: unset !important;
}

.heat_map_of_type .pepm-table .label {
    background: unset !important;
}

.heat_map_of_type .pepm-table .value {
    background: #eff1f7 !important;
}

.heat_map_of_type .select2-container {
    background: #fff !important;
}

.heat_map_of_content,.heat_map_of_fund_chart,.hys0f48pko286,.hys0f48pko494 {
    width: 100%;
}
</style>
<div class="heat_map_of_type pepm-table"></div>
<script>
    // $(function() {
    //     $("#heat_map_of_type").on("change", function(e) {
    //         var type = $(this).val();
    //         //change_content(type);
    //     });
    // });
    // //change_content('quarter');
    // function change_content(type) {
    //     layer.msg('切换中,请稍等', {
    //         icon: 16,
    //         time: false,
    //         shade: [0.3, '#000'],
    //     });
    //     $.ajax({
    //         url: '?/heat_map_of_fund/content/' + type,
    //         type: 'post',
    //         data: {},
    //         success: function(res) {
    //             $('.heat_map_of_content').html(res.html);
    //             layer.closeAll();
    //         }
    //     });
    // }
</script>
<div class="heat_map_of_content">
    <div class="heat_map_of_fund_chart">
        <div class="tuli_box_p">
            <p>季度新增项目金额</p>
            <p>季度新增项目数量</p>
        </div>
        <div class="echarts_body">
            <div class="tuli_box">
                <div class="tuli" id="project_amount3" _echarts_instance_="ec_1740042444884" style="-webkit-tap-highlight-color: transparent; user-select: none; position: relative;">
                    <div style="position: relative; width: 1110px; height: 500px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;">
                        <canvas data-zr-dom-id="zr_0" width="1110" height="500" style="position: absolute; left: 0px; top: 0px; width: 1110px; height: 500px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas>
                    </div>
                    <div style="position: absolute; display: none; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(50, 50, 50, 0.7); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font: 14px / 21px sans-serif; padding: 5px; left: 486px; top: 148px; pointer-events: none;">2017 Q1
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#06ACBC;"></span>上海医药: 0.00
                        
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#51638B;"></span>上实发展: 0.00
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#15CDAC;"></span>上实城开: 0.00
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#92E88A;"></span>上实环境: 0.00
                        
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#F0DD98;"></span>其他: 0.00
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#EC7728;"></span>平均投资金额: 0.00</div>
                </div>
            </div>
            <div class="tuli_box">
                <div class="tuli" id="number_of_items3" _echarts_instance_="ec_1740042444885" style="-webkit-tap-highlight-color: transparent; user-select: none; position: relative;">
                    <div style="position: relative; width: 1110px; height: 500px; padding: 0px; margin: 0px; border-width: 0px;">
                        <canvas data-zr-dom-id="zr_0" width="1110" height="500" style="position: absolute; left: 0px; top: 0px; width: 1110px; height: 500px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas>
                    </div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="heat_map_of_fund_table">
        <div class="table-container">
        <table class="datatables layui-table dataTable">
    <thead>
        <tr role="row">
            <th class="fixed-column">季度</th>
            <th colspan="2">2024 Q3</th>
            <th colspan="2">2024 Q2</th>
            <th colspan="2">2024 Q1</th>
            <th colspan="2">2023 Q4</th>
            <th colspan="2">2023 Q3</th>
            <th colspan="2">2023 Q2</th>
            <th colspan="2">2023 Q1</th>
            <th colspan="2">2022 Q4</th>
            <th colspan="2">2022 Q3</th>
            <th colspan="2">2022 Q2</th>
            <th colspan="2">2022 Q1</th>
            <th colspan="2">2021 Q4</th>
            <th colspan="2">2021 Q3</th>
            <th colspan="2">2021 Q2</th>
            <th colspan="2">2021 Q1</th>
        </tr>
        <tr role="row">
            <th class="fixed-column">投资领域名称</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
            <th>新增拟投资金额（万元）</th>
            <th>新增项目数量</th>
        </tr>
    </thead>
    <tbody>
        <tr class="row-weight">
            <td class="fixed-column" rowspan="2" style="text-align: center !important;">股权</td>
            <td>8,500.00</td>
            <td>2.00</td>
            <td>30,000.00</td>
            <td>1.00</td>
            <td>15,200.00</td>
            <td>3.00</td>
            <td>9,800.00</td>
            <td>2.00</td>
            <td>22,500.00</td>
            <td>4.00</td>
            <td>18,300.00</td>
            <td>3.00</td>
            <td>12,450.00</td>
            <td>2.00</td>
            <td>45,600.00</td>
            <td>5.00</td>
            <td>28,700.00</td>
            <td>3.00</td>
            <td>32,100.00</td>
            <td>4.00</td>
            <td>38,900.00</td>
            <td>5.00</td>
            <td>51,200.00</td>
            <td>6.00</td>
            <td>9,800.00</td>
            <td>2.00</td>
            <td>14,500.00</td>
            <td>3.00</td>
            <td>21,300.00</td>
            <td>4.00</td>
        </tr>
        <tr>
            <td>-62.35%</td>
            <td>+185.20%</td>
            <td>+22.50%</td>
            <td>-18.30%</td>
            <td>+15.60%</td>
            <td>-5.40%</td>
            <td>+33.20%</td>
            <td>-12.80%</td>
            <td>+28.90%</td>
            <td>-7.50%</td>
            <td>+19.30%</td>
            <td>-10.20%</td>
            <td>+45.60%</td>
            <td>-9.80%</td>
            <td>+38.70%</td>
            <td>-6.30%</td>
            <td>+27.50%</td>
            <td>-15.40%</td>
            <td>+41.20%</td>
            <td>-8.90%</td>
            <td>+53.10%</td>
            <td>-11.20%</td>
            <td>+29.80%</td>
            <td>-14.50%</td>
            <td>+18.40%</td>
            <td>-9.70%</td>
            <td>+21.30%</td>
            <td>-12.60%</td>
            <td>+53.10%</td>
            <td>-11.20%</td>
        </tr>
        <tr class="row-weight">
            <td class="fixed-column" rowspan="2" style="text-align: center !important;">固定资产</td>
            <td>12,300.00</td>
            <td>4.00</td>
            <td>8,750.00</td>
            <td>1.00</td>
            <td>23,400.00</td>
            <td>3.00</td>
            <td>17,600.00</td>
            <td>2.00</td>
            <td>29,500.00</td>
            <td>4.00</td>
            <td>14,200.00</td>
            <td>2.00</td>
            <td>9,800.00</td>
            <td>1.00</td>
            <td>38,900.00</td>
            <td>5.00</td>
            <td>22,100.00</td>
            <td>3.00</td>
            <td>41,500.00</td>
            <td>4.00</td>
            <td>33,200.00</td>
            <td>5.00</td>
            <td>57,800.00</td>
            <td>6.00</td>
            <td>11,200.00</td>
            <td>2.00</td>
            <td>18,400.00</td>
            <td>3.00</td>
            <td>27,500.00</td>
            <td>4.00</td>
        </tr>
        <tr>
            <td>+18.20%</td>
            <td>-100.00%</td>
            <td>+45.60%</td>
            <td>-22.30%</td>
            <td>+33.10%</td>
            <td>-15.40%</td>
            <td>+28.70%</td>
            <td>-9.80%</td>
            <td>+41.50%</td>
            <td>-7.20%</td>
            <td>+19.80%</td>
            <td>-12.60%</td>
            <td>+38.90%</td>
            <td>-5.55%</td>
            <td>+22.10%</td>
            <td>-8.30%</td>
            <td>+41.50%</td>
            <td>-6.70%</td>
            <td>+33.20%</td>
            <td>-9.10%</td>
            <td>+57.80%</td>
            <td>-11.20%</td>
            <td>+18.40%</td>
            <td>-7.90%</td>
            <td>+27.50%</td>
            <td>-10.60%</td>
            <td>-5.50%</td>
            <td>+22.10%</td>
            <td>-8.30%</td>
            <td>+41.50%</td>
        </tr>
        <tr class="row-weight">
            <td class="fixed-column" rowspan="2" style="text-align: center !important;">基金</td>
            <td>47,300.00</td>
            <td>11.00</td>
            <td>61,700.00</td>
            <td>11.00</td>
            <td>67,350.00</td>
            <td>12.00</td>
            <td>76,400.00</td>
            <td>13.00</td>
            <td>104,400.00</td>
            <td>17.00</td>
            <td>43,600.00</td>
            <td>10.00</td>
            <td>42,650.00</td>
            <td>11.00</td>
            <td>160,100.00</td>
            <td>11.00</td>
            <td>124,700.00</td>
            <td>9.00</td>
            <td>79,200.00</td>
            <td>11.00</td>
            <td>143,100.00</td>
            <td>16.00</td>
            <td>203,100.00</td>
            <td>13.00</td>
            <td>56,600.00</td>
            <td>10.00</td>
            <td>77,600.00</td>
            <td>14.00</td>
            <td>174,900.00</td>
            <td>14.00</td>
        </tr>
        <tr>
            <td>+225.00%</td>
            <td>-75.00%</td>
            <td>+150.00%</td>
            <td>-18.50%</td>
            <td>+33.33%</td>
            <td>-42.00%</td>
            <td>+55.00%</td>
            <td>33.33%</td>
            <td>+200.00%</td>
            <td>-75.00%</td>
            <td>0.00%</td>
            <td>-50.00%</td>
            <td>-100.00%</td>
            <td>166.67%</td>
            <td>0.00%</td>
            <td>-50.00%</td>
            <td>-64.71%</td>
            <td>+28.40%</td>
            <td>-15.30%</td>
            <td>+41.80%</td>
            <td>-9.70%</td>
            <td>+33.20%</td>
            <td>+45.50%</td>
            <td>-8.60%</td>
            <td>+38.20%</td>
            <td>-11.50%</td>
            <td>-100.00%</td>
            <td>166.67%</td>
            <td>0.00%</td>
            <td>166.67%</td>
        </tr>
        <tr class="row-weight">
            <td class="fixed-column" rowspan="2" style="text-align: center !important;">合计</td>
            <td>68,100.00</td>
            <td>17.00</td>
            <td>100,450.00</td>
            <td>13.00</td>
            <td>105,950.00</td>
            <td>18.00</td>
            <td>103,800.00</td>
            <td>17.00</td>
            <td>156,400.00</td>
            <td>25.00</td>
            <td>76,100.00</td>
            <td>15.00</td>
            <td>64,900.00</td>
            <td>14.00</td>
            <td>244,600.00</td>
            <td>21.00</td>
            <td>175,500.00</td>
            <td>15.00</td>
            <td>152,800.00</td>
            <td>19.00</td>
            <td>215,200.00</td>
            <td>26.00</td>
            <td>312,100.00</td>
            <td>25.00</td>
            <td>77,600.00</td>
            <td>14.00</td>
            <td>110,500.00</td>
            <td>20.00</td>
            <td>223,700.00</td>
            <td>22.00</td>
        </tr>
        <tr>
            <td>+21.88%</td>
            <td>+5.50%</td>
            <td>-18.30%</td>
            <td>+33.20%</td>
            <td>-12.80%</td>
            <td>+28.90%</td>
            <td>-7.50%</td>
            <td>+19.30%</td>
            <td>-10.20%</td>
            <td>+45.60%</td>
            <td>-9.80%</td>
            <td>+38.70%</td>
            <td>-6.30%</td>
            <td>+27.50%</td>
            <td>-15.40%</td>
            <td>+41.20%</td>
            <td>-8.90%</td>
            <td>+53.10%</td>
            <td>-11.20%</td>
            <td>+29.80%</td>
            <td>-14.50%</td>
            <td>+18.40%</td>
            <td>-9.70%</td>
            <td>+21.30%</td>
            <td>-12.60%</td>
            <td>+15.60%</td>
            <td>-5.40%</td>
            <td>+33.20%</td>
            <td>-8.90%</td>
            <td>+53.10%</td>
        </tr>
    </tbody>
</table>
        </div>
    </div>
    <script>
        var data = {
"value": {
    "股权": {
        "2024 Q3": { "amount": "8,500.00", "number": "2.00" },
        "2024 Q2": { "amount": "30,000.00", "number": "1.00" },
        "2024 Q1": { "amount": "15,200.00", "number": "3.00" },
        "2023 Q4": { "amount": "9,800.00", "number": "2.00" },
        "2023 Q3": { "amount": "22,500.00", "number": "4.00" },
        "2023 Q2": { "amount": "18,300.00", "number": "3.00" },
        "2023 Q1": { "amount": "12,450.00", "number": "2.00" },
        "2022 Q4": { "amount": "45,600.00", "number": "5.00" },
        "2022 Q3": { "amount": "28,700.00", "number": "3.00" },
        "2022 Q2": { "amount": "32,100.00", "number": "4.00" },
        "2022 Q1": { "amount": "38,900.00", "number": "5.00" },
        "2021 Q4": { "amount": "51,200.00", "number": "6.00" },
        "2021 Q3": { "amount": "9,800.00", "number": "2.00" },
        "2021 Q2": { "amount": "14,500.00", "number": "3.00" },
        "2021 Q1": { "amount": "21,300.00", "number": "4.00" }
    },
    "固定资产": {
        "2024 Q3": { "amount": "12,300.00", "number": "4.00" },
        "2024 Q2": { "amount": "8,750.00", "number": "1.00" },
        "2024 Q1": { "amount": "23,400.00", "number": "3.00" },
        "2023 Q4": { "amount": "17,600.00", "number": "2.00" },
        "2023 Q3": { "amount": "29,500.00", "number": "4.00" },
        "2023 Q2": { "amount": "14,200.00", "number": "2.00" },
        "2023 Q1": { "amount": "9,800.00", "number": "1.00" },
        "2022 Q4": { "amount": "38,900.00", "number": "5.00" },
        "2022 Q3": { "amount": "22,100.00", "number": "3.00" },
        "2022 Q2": { "amount": "41,500.00", "number": "4.00" },
        "2022 Q1": { "amount": "33,200.00", "number": "5.00" },
        "2021 Q4": { " atoms": "57,800.00", "number": "6.00" },
        "2021 Q3": { "amount": "11,200.00", "number": "2.00" },
        "2021 Q2": { "amount": "18,400.00", "number": "3.00" },
        "2021 Q1": { "amount": "27,500.00", "number": "4.00" }
    },
    "基金": {
        "2024 Q3": { "amount": "47,300.00", "number": "11.00" },
        "2024 Q2": { "amount": "61,700.00", "number": "11.00" },
        "2024 Q1": { "amount": "67,350.00", "number": "12.00" },
        "2023 Q4": { "amount": "76,400.00", "number": "13.00" },
        "2023 Q3": { "amount": "104,400.00", "number": "17.00" },
        "2023 Q2": { "amount": "43,600.00", "number": "10.00" },
        "2023 Q1": { "amount": "42,650.00", "number": "11.00" },
        "2022 Q4": { "amount": "160,100.00", "number": "11.00" },
        "2022 Q3": { "amount": "124,700.00", "number": "9.00" },
        "2022 Q2": { "amount": "79,200.00", "number": "11.00" },
        "2022 Q1": { "amount": "143,100.00", "number": "16.00" },
        "2021 Q4": { "amount": "203,100.00", "number": "13.00" },
        "2021 Q3": { "amount": "56,600.00", "number": "10.00" },
        "2021 Q2": { "amount": "77,600.00", "number": "14.00" },
        "2021 Q1": { "amount": "174,900.00", "number": "14.00" }
    }
},
"legend": ["股权", "固定资产", "基金"],
"xAxis": ["2021 Q1", "2021 Q2", "2021 Q3", "2021 Q4", "2022 Q1", "2022 Q2", "2022 Q3", "2022 Q4", "2023 Q1", "2023 Q2", "2023 Q3", "2023 Q4", "2024 Q1", "2024 Q2", "2024 Q3"],
"amount_lists": [
    {
        "name": "股权",
        "lists": [21300, 14500, 9800, 51200, 38900, 32100, 28700, 45600, 12450, 18300, 22500, 9800, 15200, 30000, 8500]
    },
    {
        "name": "固定资产",
        "lists": [27500, 18400, 11200, 57800, 33200, 41500, 22100, 38900, 9800, 14200, 29500, 17600, 23400, 8750, 12300]
    },
    {
        "name": "基金",
        "lists": [174900, 77600, 56600, 203100, 143100, 79200, 124700, 160100, 42650, 43600, 104400, 76400, 67350, 61700, 47300]
    }
],
"all_quarter_amount": [
    74633.33, // (21300 + 27500 + 174900) / 3
    36833.33, // (14500 + 18400 + 77600) / 3
    25533.33, // (9800 + 11200 + 56600) / 3
    104033.33, // (51200 + 57800 + 203100) / 3
    71733.33, // (38900 + 33200 + 143100) / 3
    50900.00, // (32100 + 41500 + 79200) / 3
    58533.33, // (28700 + 22100 + 124700) / 3
    81533.33, // (45600 + 38900 + 160100) / 3
    21633.33, // (12450 + 9800 + 42650) / 3
    25366.67, // (18300 + 14200 + 43600) / 3
    52166.67, // (22500 + 29500 + 104400) / 3
    34933.33, // (9800 + 17600 + 76400) / 3
    35283.33, // (15200 + 23400 + 67350) / 3
    33483.33, // (30000 + 8750 + 61700) / 3
    22600.00  // (8500 + 12300 + 47300) / 3
],
            color: ["#06ACBC", "#1F87B0", "#51638B", "#15CDAC", "#92E88A", "#C7DA5E", "#F0DD98"]
        }
        console.log(data['color'])
         var amount_lists = [];
        var color = data.color;
        data.amount_lists.forEach(function(item, index) {
            var lists = [];
            item.lists.forEach(function(item, index) {
                lists.push(item.toFixed(2));
            });
            amount_lists.push({
                name: item.name,
                type: 'bar',
                stack: 'amount',
                data: lists,
                itemStyle: {
                    normal: {
                        color: color[index]
                    },
                },
            })
        });
        var lists = [];
        data.all_quarter_amount.forEach(function(item, index) {
            lists.push(item.toFixed(2));
        });
        amount_lists.push({
            name: '平均投资金额',
            type: 'line',
            stack: 'line',
            yAxisIndex: 1,
            data: lists,
            itemStyle: {
                normal: {
                    color: '#EC7728'
                },
            },
        })
        /*
  amount_lists.push({
    name: '投后基金数量',
    type: 'line',
    stack: 'line',
    yAxisIndex: 2,
    itemStyle: {
      normal:{
        color:'rgba(128, 128, 128, 0)',
      },
    },
    data:data.touhou_fund_amount
  });
  */
         // 指定图表的配置项和数据
         var str_amount_option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: { // 坐标轴指示器，坐标轴触发有效
                    type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            legend: {
                data: data.legend,
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '10%',
                top: '18%',
                containLabel: true
            },
            xAxis: [{
                type: 'category',
                data: data.xAxis
            }],
            yAxis: [{
                type: 'value',
                name: '金额（万）'
            }, {
                type: 'value',
                name: '平均交易金额（万）',
                axisLabel: {
                    formatter: '{value}'
                }
            }, {
                type: 'value',
                axisLabel: {
                    show: false // 设置 y 轴标签不显示
                }
            }, ],
            dataZoom: [{
                type: 'slider',
                xAxisIndex: 0,
                filterMode: 'empty'
            }, {
                start: 0,
                end: 100,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                    color: '#fff',
                    shadowBlur: 3,
                    shadowColor: 'rgba(0, 0, 0, 0.6)',
                    shadowOffsetX: 2,
                    shadowOffsetY: 2
                }
            }],
            series: amount_lists
        };
        var str_amount3 = echarts.init(document.getElementById("project_amount3"));
        str_amount3.setOption(str_amount_option, true);
        var data = {
    value: parse_json('{"股权":{"2021 Q1":{"amount":"21,300.00","number":"4.00"},"2021 Q2":{"amount":"14,500.00","number":"3.00"},"2021 Q3":{"amount":"9,800.00","number":"2.00"},"2021 Q4":{"amount":"51,200.00","number":"6.00"},"2022 Q1":{"amount":"38,900.00","number":"5.00"},"2022 Q2":{"amount":"32,100.00","number":"4.00"},"2022 Q3":{"amount":"28,700.00","number":"3.00"},"2022 Q4":{"amount":"45,600.00","number":"5.00"},"2023 Q1":{"amount":"12,450.00","number":"2.00"},"2023 Q2":{"amount":"18,300.00","number":"3.00"},"2023 Q3":{"amount":"22,500.00","number":"4.00"},"2023 Q4":{"amount":"9,800.00","number":"2.00"},"2024 Q1":{"amount":"15,200.00","number":"3.00"},"2024 Q2":{"amount":"30,000.00","number":"1.00"},"2024 Q3":{"amount":"8,500.00","number":"2.00"}},"固定资产":{"2021 Q1":{"amount":"27,500.00","number":"4.00"},"2021 Q2":{"amount":"18,400.00","number":"3.00"},"2021 Q3":{"amount":"11,200.00","number":"2.00"},"2021 Q4":{"amount":"57,800.00","number":"6.00"},"2022 Q1":{"amount":"33,200.00","number":"5.00"},"2022 Q2":{"amount":"41,500.00","number":"4.00"},"2022 Q3":{"amount":"22,100.00","number":"3.00"},"2022 Q4":{"amount":"38,900.00","number":"5.00"},"2023 Q1":{"amount":"9,800.00","number":"1.00"},"2023 Q2":{"amount":"14,200.00","number":"2.00"},"2023 Q3":{"amount":"29,500.00","number":"4.00"},"2023 Q4":{"amount":"17,600.00","number":"2.00"},"2024 Q1":{"amount":"23,400.00","number":"3.00"},"2024 Q2":{"amount":"8,750.00","number":"1.00"},"2024 Q3":{"amount":"12,300.00","number":"4.00"}},"基金":{"2021 Q1":{"amount":"174,900.00","number":"14.00"},"2021 Q2":{"amount":"77,600.00","number":"14.00"},"2021 Q3":{"amount":"56,600.00","number":"10.00"},"2021 Q4":{"amount":"203,100.00","number":"13.00"},"2022 Q1":{"amount":"143,100.00","number":"16.00"},"2022 Q2":{"amount":"79,200.00","number":"11.00"},"2022 Q3":{"amount":"124,700.00","number":"9.00"},"2022 Q4":{"amount":"160,100.00","number":"11.00"},"2023 Q1":{"amount":"42,650.00","number":"11.00"},"2023 Q2":{"amount":"43,600.00","number":"10.00"},"2023 Q3":{"amount":"104,400.00","number":"17.00"},"2023 Q4":{"amount":"76,400.00","number":"13.00"},"2024 Q1":{"amount":"67,350.00","number":"12.00"},"2024 Q2":{"amount":"61,700.00","number":"11.00"},"2024 Q3":{"amount":"47,300.00","number":"11.00"}}}'),
    legend: parse_json('["股权","固定资产","基金"]'),
    xAxis: parse_json('["2021 Q1","2021 Q2","2021 Q3","2021 Q4","2022 Q1","2022 Q2","2022 Q3","2022 Q4","2023 Q1","2023 Q2","2023 Q3","2023 Q4","2024 Q1","2024 Q2","2024 Q3"]'),
    number_lists: parse_json('[{"name":"股权","lists":[4,3,2,6,5,4,3,5,2,3,4,2,3,1,2]},{"name":"固定资产","lists":[4,3,2,6,5,4,3,5,1,2,4,2,3,1,4]},{"name":"基金","lists":[14,14,10,13,16,11,9,11,11,10,17,13,12,11,11]}]'),
    all_quarter_number: parse_json('[7.33,6.67,4.67,8.33,8.67,6.33,5.00,7.00,4.67,5.00,8.33,5.67,6.00,4.33,5.67]')
};
        var number_lists = [];
        data.number_lists.forEach(function(item, index) {
            var lists = [];
            item.lists.forEach(function(item, index) {
                lists.push(item.toFixed(2));
            });
            number_lists.push({
                name: item.name,
                type: 'bar',
                stack: 'amount',
                data: lists,
                itemStyle: {
                    normal: {
                        color: color[index]
                    },
                },
            })
        });
        var lists = [];
        data.all_quarter_number.forEach(function(item, index) {
            lists.push(item.toFixed(2));
        });
        number_lists.push({
            name: '均值',
            type: 'line',
            stack: 'line',
            yAxisIndex: 1,
            data: lists,
            itemStyle: {
                normal: {
                    color: '#EC7728'
                },
            },
        })
         console.log(number_lists)
        /*
  number_lists.push({
    name: '投后基金数量',
    type: 'scatter',
    stack: 'scatter',
    yAxisIndex: 2,
    itemStyle: {
      normal:{
        color:'rgba(128, 128, 128, 0)',
      },
    },
    data:data.touhou_fund_number
  });
  
   */
         // 指定图表的配置项和数据
         var str_number_option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: { // 坐标轴指示器，坐标轴触发有效
                    type: 'shadow' // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            legend: {
                data: data.legend,
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '10%',
                top: '18%',
                containLabel: true
            },
            xAxis: [{
                type: 'category',
                data: data.xAxis
            }],
            yAxis: [{
                type: 'value',
                name: '数量'
            }, {
                type: 'value',
                name: '均值（个）',
                axisLabel: {
                    formatter: '{value}'
                }
            }, {
                type: 'value',
                axisLabel: {
                    show: false // 设置 y 轴标签不显示
                }
            }],
            dataZoom: [{
                type: 'slider',
                xAxisIndex: 0,
                filterMode: 'empty'
            }, {
                start: 0,
                end: 100,
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                    color: '#fff',
                    shadowBlur: 3,
                    shadowColor: 'rgba(0, 0, 0, 0.6)',
                    shadowOffsetX: 2,
                    shadowOffsetY: 2
                }
            }],
            series: number_lists,
        };
        var number_of_items_id = 'number_of_items3';
        var str_number3 = echarts.init(document.getElementById(number_of_items_id));
        str_number3.setOption(str_number_option, true);
    </script>

    <script>
        $(function(){
            
            $("li[kid='hywth5e9dy']").on('click',function(){
                setTimeout(function(){
                str_number3.resize();
                str_amount3.resize();
            });
            });
        });
    </script>
</div>