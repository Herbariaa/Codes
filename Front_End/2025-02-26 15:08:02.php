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

.heat_map_of_fund_chart .echarts_body .tuli_box #project_amount2,.heat_map_of_fund_chart .echarts_body .tuli_box #number_of_items2 {
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
                <div class="tuli" id="project_amount2" _echarts_instance_="ec_1740042444884" style="-webkit-tap-highlight-color: transparent; user-select: none; position: relative;">
                    <div style="position: relative; width: 1110px; height: 500px; padding: 0px; margin: 0px; border-width: 0px; cursor: default;">
                        <canvas data-zr-dom-id="zr_0" width="1110" height="500" style="position: absolute; left: 0px; top: 0px; width: 1110px; height: 500px; user-select: none; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); padding: 0px; margin: 0px; border-width: 0px;"></canvas>
                    </div>
                    <div style="position: absolute; display: none; border-style: solid; white-space: nowrap; z-index: 9999999; transition: left 0.4s cubic-bezier(0.23, 1, 0.32, 1), top 0.4s cubic-bezier(0.23, 1, 0.32, 1); background-color: rgba(50, 50, 50, 0.7); border-width: 0px; border-color: rgb(51, 51, 51); border-radius: 4px; color: rgb(255, 255, 255); font: 14px / 21px sans-serif; padding: 5px; left: 486px; top: 148px; pointer-events: none;">2017 Q1
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#06ACBC;"></span>长期股权投资: 0.00
                        
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#51638B;"></span>固定资产投资: 0.00
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#15CDAC;"></span>土地投资: 0.00
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#92E88A;"></span>基金投资: 0.00
                        
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#F0DD98;"></span>金融市场投资: 0.00
                        <br><span style="display:inline-block;margin-right:5px;border-radius:10px;width:10px;height:10px;background-color:#EC7728;"></span>平均投资金额: 0.00</div>
                </div>
            </div>
            <div class="tuli_box">
                <div class="tuli" id="number_of_items2" _echarts_instance_="ec_1740042444885" style="-webkit-tap-highlight-color: transparent; user-select: none; position: relative;">
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
                    <th class="fixed-column">五大行业名称</th>
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
                    <td class="fixed-column" rowspan="2" style="text-align: center !important;">长期股权投资</td>
                    <td>10,000.00</td>
                    <td>3.00</td>
                    <td>28,000.00</td>
                    <td>2.00</td>
                    <td>14,200.00</td>
                    <td>4.00</td>
                    <td>8,500.00</td>
                    <td>3.00</td>
                    <td>20,000.00</td>
                    <td>5.00</td>
                    <td>16,300.00</td>
                    <td>4.00</td>
                    <td>11,450.00</td>
                    <td>3.00</td>
                    <td>42,600.00</td>
                    <td>6.00</td>
                    <td>26,700.00</td>
                    <td>4.00</td>
                    <td>30,100.00</td>
                    <td>5.00</td>
                    <td>36,900.00</td>
                    <td>6.00</td>
                    <td>48,200.00</td>
                    <td>7.00</td>
                    <td>8,500.00</td>
                    <td>3.00</td>
                    <td>13,500.00</td>
                    <td>4.00</td>
                    <td>19,300.00</td>
                    <td>5.00</td>
                </tr>
                <tr>
                    <td>-50.00%</td>
                    <td>+150.00%</td>
                    <td>+18.50%</td>
                    <td>-20.00%</td>
                    <td>+12.60%</td>
                    <td>-6.40%</td>
                    <td>+31.20%</td>
                    <td>-11.80%</td>
                    <td>+26.90%</td>
                    <td>-6.50%</td>
                    <td>+17.30%</td>
                    <td>-9.20%</td>
                    <td>+42.60%</td>
                    <td>-8.80%</td>
                    <td>+36.70%</td>
                    <td>-5.30%</td>
                    <td>+25.50%</td>
                    <td>-14.40%</td>
                    <td>+39.20%</td>
                    <td>-7.90%</td>
                    <td>+51.10%</td>
                    <td>-10.20%</td>
                    <td>+27.80%</td>
                    <td>-13.50%</td>
                    <td>+16.40%</td>
                    <td>-8.70%</td>
                    <td>+19.30%</td>
                    <td>-11.60%</td>
                    <td>+51.10%</td>
                    <td>-10.20%</td>
                </tr>

                <tr class="row-weight">
                    <td class="fixed-column" rowspan="2" style="text-align: center !important;">固定资产投资</td>
                    <td>11,300.00</td>
                    <td>5.00</td>
                    <td>7,750.00</td>
                    <td>2.00</td>
                    <td>21,400.00</td>
                    <td>4.00</td>
                    <td>16,600.00</td>
                    <td>3.00</td>
                    <td>27,500.00</td>
                    <td>5.00</td>
                    <td>13,200.00</td>
                    <td>3.00</td>
                    <td>8,800.00</td>
                    <td>2.00</td>
                    <td>36,900.00</td>
                    <td>6.00</td>
                    <td>20,100.00</td>
                    <td>4.00</td>
                    <td>39,500.00</td>
                    <td>5.00</td>
                    <td>31,200.00</td>
                    <td>6.00</td>
                    <td>55,800.00</td>
                    <td>7.00</td>
                    <td>10,200.00</td>
                    <td>3.00</td>
                    <td>17,400.00</td>
                    <td>4.00</td>
                    <td>25,500.00</td>
                    <td>5.00</td>
                </tr>
                <tr>
                    <td>+16.20%</td>
                    <td>-80.00%</td>
                    <td>+42.60%</td>
                    <td>-21.30%</td>
                    <td>+30.10%</td>
                    <td>-14.40%</td>
                    <td>+26.70%</td>
                    <td>-8.80%</td>
                    <td>+39.50%</td>
                    <td>-6.20%</td>
                    <td>+17.80%</td>
                    <td>-11.60%</td>
                    <td>+36.90%</td>
                    <td>-4.50%</td>
                    <td>+20.10%</td>
                    <td>-7.30%</td>
                    <td>+39.50%</td>
                    <td>-5.70%</td>
                    <td>+31.200%</td>
                    <td>-8.10%</td>
                    <td>+55.800%</td>
                    <td>-10.20%</td>
                    <td>+17.400%</td>
                    <td>-6.90%</td>
                    <td>+25.500%</td>
                    <td>-9.60%</td>
                    <td>-4.500%</td>
                    <td>+20.100%</td>
                    <td>-7.300%</td>
                    <td>+39.500%</td>
                </tr>

                <tr class="row-weight">
                    <td class="fixed-column" rowspan="2" style="text-align: center !important;">土地投资</td>
                    <td>14,000.00</td>
                    <td>2.00</td>
                    <td>40,300.00</td>
                    <td>4.00</td>
                    <td>27,750.00</td>
                    <td>3.00</td>
                    <td>36,600.00</td>
                    <td>5.00</td>
                    <td>50,200.00</td>
                    <td>6.00</td>
                    <td>18,800.00</td>
                    <td>3.00</td>
                    <td>32,450.00</td>
                    <td>5.00</td>
                    <td>87,900.00</td>
                    <td>4.00</td>
                    <td>71,100.00</td>
                    <td>2.00</td>
                    <td>64,400.00</td>
                    <td>5.00</td>
                    <td>91,300.00</td>
                    <td>5.00</td>
                    <td>119,000.00</td>
                    <td>4.00</td>
                    <td>44,500.00</td>
                    <td>4.00</td>
                    <td>37,200.00</td>
                    <td>7.00</td>
                    <td>119,000.00</td>
                    <td>4.00</td>
                </tr>
                <tr>
                    <td>+200.00%</td>
                    <td>-70.00%</td>
                    <td>+140.00%</td>
                    <td>-17.50%</td>
                    <td>+30.33%</td>
                    <td>-40.00%</td>
                    <td>+52.00%</td>
                    <td>30.33%</td>
                    <td>+190.00%</td>
                    <td>-70.00%</td>
                    <td>0.00%</td>
                    <td>-45.00%</td>
                    <td>-90.00%</td>
                    <td>160.67%</td>
                    <td>0.00%</td>
                    <td>-45.00%</td>
                    <td>-60.71%</td>
                    <td>+26.40%</td>
                    <td>-14.30%</td>
                    <td>+39.80%</td>
                    <td>-8.70%</td>
                    <td>+31.20%</td>
                    <td>-11.10%</td>
                    <td>+43.50%</td>
                    <td>-7.60%</td>
                    <td>+36.200%</td>
                    <td>-10.500%</td>
                    <td>-90.000%</td>
                    <td>160.670%</td>
                    <td>0.000%</td>
                </tr>

                <tr class="row-weight">
                    <td class="fixed-column" rowspan="2" style="text-align: center !important;">基金投资</td>
                    <td>24,000.00</td>
                    <td>4.00</td>
                    <td>17,900.00</td>
                    <td>3.00</td>
                    <td>31,400.00</td>
                    <td>5.00</td>
                    <td>26,500.00</td>
                    <td>4.00</td>
                    <td>40,800.00</td>
                    <td>6.00</td>
                    <td>21,100.00</td>
                    <td>3.00</td>
                    <td>9,000.00</td>
                    <td>2.00</td>
                    <td>55,700.00</td>
                    <td>2.00</td>
                    <td>47,200.00</td>
                    <td>3.00</td>
                    <td>9,000.00</td>
                    <td>2.00</td>
                    <td>38,800.00</td>
                    <td>4.00</td>
                    <td>62,400.00</td>
                    <td>3.00</td>
                    <td>9,000.00</td>
                    <td>2.00</td>
                    <td>27,500.00</td>
                    <td>3.00</td>
                    <td>51,300.00</td>
                    <td>5.00</td>
                </tr>
                <tr>
                    <td>+45.00%</td>
                    <td>-20.00%</td>
                    <td>+15.00%</td>
                    <td>-90.00%</td>
                    <td>-90.00%</td>
                    <td>+75.00%</td>
                    <td>+42.60%</td>
                    <td>-21.30%</td>
                    <td>+30.10%</td>
                    <td>-14.40%</td>
                    <td>+26.70%</td>
                    <td>-8.80%</td>
                    <td>+39.50%</td>
                    <td>-6.20%</td>
                    <td>+17.800%</td>
                    <td>-11.600%</td>
                    <td>+36.900%</td>
                    <td>-4.500%</td>
                    <td>+20.100%</td>
                    <td>-7.300%</td>
                    <td>+39.500%</td>
                    <td>-5.700%</td>
                    <td>+31.200%</td>
                    <td>-8.100%</td>
                    <td>+55.800%</td>
                    <td>-10.200%</td>
                    <td>+30.100%</td>
                    <td>-14.400%</td>
                    <td>+26.700%</td>
                    <td>-8.800%</td>
                </tr>

                <tr class="row-weight">
                    <td class="fixed-column" rowspan="2" style="text-align: center !important;">金融市场投资</td>
                    <td>8,800.00</td>
                    <td>3.00</td>
                    <td>6,500.00</td>
                    <td>2.00</td>
                    <td>11,300.00</td>
                    <td>2.00</td>
                    <td>14,600.00</td>
                    <td>2.00</td>
                    <td>17,900.00</td>
                    <td>3.00</td>
                    <td>5,700.00</td>
                    <td>2.00</td>
                    <td>3,200.00</td>
                    <td>2.00</td>
                    <td>21,500.00</td>
                    <td>4.00</td>
                    <td>12,400.00</td>
                    <td>2.00</td>
                    <td>8,800.00</td>
                    <td>2.00</td>
                    <td>16,200.00</td>
                    <td>3.00</td>
                    <td>27,700.00</td>
                    <td>4.00</td>
                    <td>4,400.00</td>
                    <td>2.00</td>
                    <td>7,900.00</td>
                    <td>2.00</td>
                    <td>11,300.00</td>
                    <td>3.00</td>
                </tr>
                <tr>
                    <td>+12.00%</td>
                    <td>-90.00%</td>
                    <td>0.00%</td>
                    <td>+20.50%</td>
                    <td>-17.30%</td>
                    <td>+14.60%</td>
                    <td>-4.40%</td>
                    <td>+31.20%</td>
                    <td>-11.80%</td>
                    <td>+26.90%</td>
                    <td>-6.50%</td>
                    <td>+17.300%</td>
                    <td>-9.200%</td>
                    <td>+42.600%</td>
                    <td>-8.800%</td>
                    <td>+36.70%</td>
                    <td>-5.30%</td>
                    <td>+25.50%</td>
                    <td>-14.40%</td>
                    <td>+39.20%</td>
                    <td>-7.90%</td>
                    <td>+51.10%</td>
                    <td>-10.20%</td>
                    <td>+27.80%</td>
                    <td>-13.50%</td>
                    <td>+16.40%</td>
                    <td>-8.70%</td>
                    <td>+19.30%</td>
                    <td>-11.60%</td>
                    <td>+14.60%</td>
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
                <>
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
                "长期股权投资": {
                    "2024 Q3": { "amount": "10,000.00", "number": "3.00" },
                    "2024 Q2": { "amount": "28,000.00", "number": "2.00" },
                    "2024 Q1": { "amount": "14,200.00", "number": "4.00" },
                    "2023 Q4": { "amount": "8,500.00", "number": "3.00" },
                    "2023 Q3": { "amount": "20,000.00", "number": "5.00" },
                    "2023 Q2": { "amount": "16,300.00", "number": "4.00" },
                    "2023 Q1": { "amount": "11,450.00", "number": "3.00" },
                    "2022 Q4": { "amount": "42,600.00", "number": "6.00" },
                    "2022 Q3": { "amount": "26,700.00", "number": "4.00" },
                    "2022 Q2": { "amount": "30,100.00", "number": "5.00" },
                    "2022 Q1": { "amount": "36,900.00", "number": "6.00" },
                    "2021 Q4": { "amount": "48,200.00", "number": "7.00" },
                    "2021 Q3": { "amount": "8,500.00", "number": "3.00" },
                    "2021 Q2": { "amount": "13,500.00", "number": "4.00" },
                    "2021 Q1": { "amount": "19,300.00", "number": "5.00" }
                },
                "固定资产投资": {
                    "2024 Q3": { "amount": "11,300.00", "number": "5.00" },
                    "2024 Q2": { "amount": "7,750.00", "number": "2.00" },
                    "2024 Q1": { "amount": "21,400.00", "number": "4.00" },
                    "2023 Q4": { "amount": "16,600.00", "number": "3.00" },
                    "2023 Q3": { "amount": "27,500.00", "number": "5.00" },
                    "2023 Q2": { "amount": "13,200.00", "number": "3.00" },
                    "2023 Q1": { "amount": "8,800.00", "number": "2.00" },
                    "2022 Q4": { "amount": "36,900.00", "number": "6.00" },
                    "2022 Q3": { "amount": "20,100.00", "number": "4.00" },
                    "2022 Q2": { "amount": "39,500.00", "number": "5.00" },
                    "2022 Q1": { "amount": "31,200.00", "number": "6.00" },
                    "2021 Q4": { "amount": "55,800.00", "number": "7.00" },
                    "2021 Q3": { "amount": "10,200.00", "number": "3.00" },
                    "2021 Q2": { "amount": "17,400.00", "number": "4.00" },
                    "2021 Q1": { "amount": "25,500.00", "number": "5.00" }
                },
                "土地投资": {
                    "2024 Q3": { "amount": "14,000.00", "number": "2.00" },
                    "2024 Q2": { "amount": "40,300.00", "number": "4.00" },
                    "2024 Q1": { "amount": "27,750.00", "number": "3.00" },
                    "2023 Q4": { "amount": "36,600.00", "number": "5.00" },
                    "2023 Q3": { "amount": "50,200.00", "number": "6.00" },
                    "2023 Q2": { "amount": "18,800.00", "number": "3.00" },
                    "2023 Q1": { "amount": "32,450.00", "number": "5.00" },
                    "2022 Q4": { "amount": "87,900.00", "number": "4.00" },
                    "2022 Q3": { "amount": "71,100.00", "number": "2.00" },
                    "2022 Q2": { "amount": "64,400.00", "number": "5.00" },
                    "2022 Q1": { "amount": "91,300.00", "number": "5.00" },
                    "2021 Q4": { "amount": "119,000.00", "number": "4.00" },
                    "2021 Q3": { "amount": "44,500.00", "number": "4.00" },
                    "2021 Q2": { "amount": "37,200.00", "number": "7.00" },
                    "2021 Q1": { "amount": "119,000.00", "number": "4.00" }
                },
                "基金投资": {
                    "2024 Q3": { "amount": "24,000.00", "number": "4.00" },
                    "2024 Q2": { "amount": "17,900.00", "number": "3.00" },
                    "2024 Q1": { "amount": "31,400.00", "number": "5.00" },
                    "2023 Q4": { "amount": "26,500.00", "number": "4.00" },
                    "2023 Q3": { "amount": "40,800.00", "number": "6.00" },
                    "2023 Q2": { "amount": "21,100.00", "number": "3.00" },
                    "2023 Q1": { "amount": "9,000.00", "number": "2.00" },
                    "2022 Q4": { "amount": "55,700.00", "number": "2.00" },
                    "2022 Q3": { "amount": "47,200.00", "number": "3.00" },
                    "2022 Q2": { "amount": "9,000.00", "number": "2.00" },
                    "2022 Q1": { "amount": "38,800.00", "number": "4.00" },
                    "2021 Q4": { "amount": "62,400.00", "number": "3.00" },
                    "2021 Q3": { "amount": "9,000.00", "number": "2.00" },
                    "2021 Q2": { "amount": "27,500.00", "number": "3.00" },
                    "2021 Q1": { "amount": "51,300.00", "number": "5.00" }
                },
                "金融市场投资": {
                    "2024 Q3": { "amount": "8,800.00", "number": "3.00" },
                    "2024 Q2": { "amount": "6,500.00", "number": "2.00" },
                    "2024 Q1": { "amount": "11,300.00", "number": "2.00" },
                    "2023 Q4": { "amount": "14,600.00", "number": "2.00" },
                    "2023 Q3": { "amount": "17,900.00", "number": "3.00" },
                    "2023 Q2": { "amount": "5,700.00",  "number": "2.00" },
                    "2023 Q1": { "amount": "3,200.00", "number": "2.00" },
                    "2022 Q4": { "amount": "21,500.00", "number": "4.00" },
                    "2022 Q3": { "amount": "12,400.00", "number": "2.00" },
                    "2022 Q2": { "amount": "8,800.00", "number": "2.00" },
                    "2022 Q1": { "amount": "16,200.00", "number": "3.00" },
                    "2021 Q4": { "amount": "27,700.00", "number": "4.00" },
                    "2021 Q3": { "amount": "4,400.00", "number": "2.00" },
                    "2021 Q2": { "amount": "7,900.00", "number": "2.00" },
                    "2021 Q1": { "amount": "11,300.00", "number": "3.00" }
                }
            },
            legend: ["长期股权投资", "固定资产投资", "土地投资", "基金投资", "金融市场投资"],
            xAxis: ["2021 Q1", "2021 Q2", "2021 Q3", "2021 Q4", "2022 Q1", "2022 Q2", "2022 Q3", "2022 Q4", "2023 Q1", "2023 Q2", "2023 Q3", "2023 Q4", "2024 Q1", "2024 Q2", "2024 Q3"],
            amount_lists: [{
                "name": "长期股权投资",
                "lists": [19300, 13500, 8500, 48200, 36900, 30100, 26700, 42600, 11450, 16300, 20000, 8500, 14200, 28000, 10000]
            }, {
                "name": "固定资产投资",
                "lists": [25500, 17400, 10200, 55800, 31200, 39500, 20100, 36900, 8800, 13200, 27500, 16600, 21400, 7750, 11300]
            }, {
                "name": "土地投资",
                "lists": [119000, 37200, 44500, 119000, 91300, 64400, 71100, 87900, 32450, 18800, 50200, 36600, 27750, 40300, 14000]
            }, {
                "name": "基金投资",
                "lists": [51300, 27500, 9000, 62400, 38800, 9000, 47200, 55700, 9000, 21100, 40800, 26500, 31400, 17900, 24000]
            }, {
                "name": "金融市场投资",
                "lists": [11300, 7900, 4400, 27700, 16200, 8800, 12400, 21500, 3200, 5700, 17900, 14600, 11300, 6500, 8800]
            }],
            all_quarter_amount: [
              47060, 22100, 15520, 62420, 43040, 30560, 35100, 48920, 12980, 15220, 31280, 20760, 21190, 20090, 13620
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
        var str_amount2 = echarts.init(document.getElementById("project_amount2"));
        str_amount2.setOption(str_amount_option, true);
        var data = {
            value: {
                "长期股权投资": {
                    "2021 Q1": { "amount": "19,300.00", "number": "5.00" },
                    "2021 Q2": { "amount": "13,500.00", "number": "4.00" },
                    "2021 Q3": { "amount": "8,500.00", "number": "3.00" },
                    "2021 Q4": { "amount": "48,200.00", "number": "7.00" },
                    "2022 Q1": { "amount": "36,900.00", "number": "6.00" },
                    "2022 Q2": { "amount": "30,100.00", "number": "5.00" },
                    "2022 Q3": { "amount": "26,700.00", "number": "4.00" },
                    "2022 Q4": { "amount": "42,600", "number": "6.00" },
                    "2023 Q1": { "amount": "11,450.00", "number": "3.00" },
                    "2023 Q2": { "amount": "16,300.00", "number": "4.00" },
                    "2023 Q3": { "amount": "20,000.00", "number": "5.00" },
                    "2023 Q4": { "amount": "8,500.00", "number": "3.00" },
                    "2024 Q1": { "amount": "14,200.00", "number": "4.00" },
                    "2024 Q2": { "amount": "28,000.00", "number": "2.00" },
                    "2024 Q3": { "amount": "10,000.00", "number": "3.00" }
                },
                "固定资产投资": {
                    "2021 Q1": { "amount": "25,500.00", "number": "5.00" },
                    "2021 Q2": { "amount": "17,400.00", "number": "4.00" },
                    "2021 Q3": { "amount": "10,200.00", "number": "3.00" },
                    "2021 Q4": { "amount": "55,800.00", "number": "7.00" },
                    "2022 Q1": { "amount": "31,200.00", "number": "6.00" },
                    "2022 Q2": { "amount": "39,500.00", "number": "5.00" },
                    "2022 Q3": { "amount": "20,100.00", "number": "4.00" },
                    "2022 Q4": { "amount": "36,900.00", "number": "6.00" },
                    "2023 Q1": { "amount": "8,800.00", "number": "2.00" },
                    "2023 Q2": { "amount": "13,200.00", "number": "3.00" },
                    "2023 Q3": { "amount": "27,500.00", "number": "5.00" },
                    "2023 Q4": { "amount": "16,600.00", "number": "3.00" },
                    "2024 Q1": { "amount": "21,400.00", "number": "4.00" },
                    "2024 Q2": { "amount": "7,750.00", "number": "2.00" },
                    "2024 Q3": { "amount": "11,300.00", "number": "5.00" }
                },
                "土地投资": {
                    "2021 Q1": { "amount": "119,000.00", "number": "4.00" },
                    "2021 Q2": { "amount": "37,200.00", "number": "7.00" },
                    "2021 Q3": { "amount": "44,500.00", "number": "4.00" },
                    "2021 Q4": { "amount": "119,000.00", "number": "4.00" },
                    "2022 Q1": { "amount": "91,300.00", "number": "5.00" },
                    "2022 Q2": { "amount": "64,400.00", "number": "5.00" },
                    "2022 Q3": { "amount": "71,100.00", "number": "2.00" },
                    "2022 Q4": { "amount": "87,900.00", "number": "4.00" },
                    "2023 Q1": { "amount": "32,450.00", "number": "5.00" },
                    "2023 Q2": { "amount": "18,800.00", "number": "3.00" },
                    "2023 Q3": { "amount": "50,200.00", "number": "6.00" },
                    "2023 Q4": { "amount": "36,600.00", "number": "5.00" },
                    "2024 Q1": { "amount": "27,750.00", "number": "3.00" },
                    "2024 Q2": { "amount": "40,300.00", "number": "4.00" },
                    "2024 Q3": { "amount": "14,000.00", "number": "2.00" }
                },
                "基金投资": {
                    "2021 Q1": { "amount": "51,300.00", "number": "5.00" },
                    "2021 Q2": { "amount": "27,500.00", "number": "3.00" },
                    "2021 Q3": { "amount": "9,000.00", "number": "2.00" },
                    "2021 Q4": { "amount": "62,400.00", "number": "3.00" },
                    "2022 Q1": { "amount": "38,800.00", "number": "4.00" },
                    "2022 Q2": { "amount": "9,000.00", "number": "2.00" },
                    "2022 Q3": { "amount": "47,200.00", "number": "3.00" },
                    "2022 Q4": { "amount": "55,700.00", "number": "2.00" },
                    "2023 Q1": { "amount": "9,000.00", "number": "2.00" },
                    "2023 Q2": { "amount": "21,100.00", "number": "3.00" },
                    "2023 Q3": { "amount": "40,800.00", "number": "6.00" },
                    "2023 Q4": { "amount": "26,500.00", "number": "4.00" },
                    "2024 Q1": { "amount": "31,400.00", "number": "5.00" },
                    "2024 Q2": { "amount": "17,900.00", "number": "3.00" },
                    "2024 Q3": { "amount": "24,000.00", "number": "4.00" }
                },
                "金融市场投资": {
                    "2021 Q1": { "amount": "11,300.00", "number": "3.00" },
                    "2021 Q2": { "amount": "7,900.00", "number": "2.00" },
                    "2021 Q3": { "amount": "4,400.00", "number": "2.00" },
                    "2021 Q4": { "amount": "27,700.00", "number": "4.00" },
                    "2022 Q1": { "amount": "16,200.00", "number": "3.00" },
                    "2022 Q2": { "amount": "8,800.00", "number": "2.00" },
                    "2022 Q3": { "amount": "12,400.00", "number": "2.00" },
                    "2022 Q4": { "amount": "21,500.00", "number": "4.00" },
                    "2023 Q1": { "amount": "3,200.00", "number": "2.00" },
                    "2023 Q2": { "amount": "5,700.00", "number": "2.00" },
                    "2023 Q3": { "amount": "17,900.00", "number": "3.00" },
                    "2023 Q4": { "amount": "14,600.00", "number": "2.00" },
                    "2024 Q1": { "amount": "11,300.00", "number": "2.00" },
                    "2024 Q2": { "amount": "6,500.00", "number": "2.00" },
                    "2024 Q3": { "amount": "8,800.00", "number": "3.00" }
                }
            },
            legend: ["长期股权投资", "固定资产投资", "土地投资", "基金投资", "金融市场投资"],
            xAxis: ["2021 Q1", "2021 Q2", "2021 Q3", "2021 Q4", "2022 Q1", "2022 Q2", "2022 Q3", "2022 Q4", "2023 Q1", "2023 Q2", "2023 Q3", "2023 Q4", "2024 Q1", "2024 Q2", "2024 Q3"],
            number_lists: [
                {
                    "name": "长期股权投资",
                    "lists": [5, 4, 3, 7, 6, 5, 4, 6, 3, 4, 5, 3, 4, 2, 3]
                },
                {
                    "name": "固定资产投资",
                    "lists": [5, 4, 3, 7, 6, 5, 4, 6, 2, 3, 5, 3, 4, 2, 5]
                },
                {
                    "name": "土地投资",
                    "lists": [4, 7, 4, 4, 5, 5, 2, 4, 5, 3, 6, 5, 3, 4, 2]
                },
                {
                    "name": "基金投资",
                    "lists": [5, 3, 2, 3, 4, 2, 3, 2, 2, 3, 6, 4, 5, 3, 4]
                },
                {
                    "name": "金融市场投资",
                    "lists": [3, 2, 2, 4, 3, 2, 2, 4, 2, 2, 3, 2, 2, 2, 3]
                }
            ],
            all_quarter_number: [
                4.4, 4.0, 3.0, 5.0, 4.8, 3.8, 3.0, 4.4, 3.0, 2.8, 4.6, 3.4, 3.6, 2.2, 3.6
            ]
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
        var number_of_items_id = 'number_of_items2';
        var str_number2 = echarts.init(document.getElementById(number_of_items_id));
        str_number2.setOption(str_number_option, true);
    </script>

    <script>
        $(function(){
            
            $("li[kid='hywth5e9dy']").on('click',function(){
                setTimeout(function(){
                str_number2.resize();
                str_amount2.resize();
            });
            });
        });
    </script>
</div>