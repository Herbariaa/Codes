# 省内外车牌判断
def fenlei(ID):
    area = ID[1]  # 获取车牌号码的第 0 个字符
    print("车牌首字符:", area)
    if area == '云':  # 假设以'云'开头的车牌为省内车辆
        return "省内车辆"
    else:
        return "省外车辆"

# 列表carids存放待统计车辆的车牌
carids = ['云EV34457', '云P87004', '京A87C3A', '沪JAYF09', '云JAYF09', '云JAYF09']  # 示例车牌号码列表

num1 = num2 = 0  # 初始化省内和省外车辆数量为0
for carid in carids:
    if fenlei(carid) == "省内车辆":  # 使用fenlei函数判断车辆属于省内还是省外
        num1 = num1 + 1
    else:
        num2 = num2 + 1

print("省内车辆数是：", num1, "省外车辆数是：", num2)
