# 解决“百分百鸡”问题
solutions = []

for x in range(0, 21):  # 公鸡数量从0到20
    for y in range(0, 34):  # 母鸡数量从0到33
        z = 100 - x - y  # 小鸡数量由总数减去公鸡和母鸡的数量
        if 5 * x + 3 * y + z / 3 == 100:  # 总积分等于100
            solutions.append((x, y, z))

# 输出所有可能的解决方案
for x, y, z in solutions:
    print("公鸡:", x, "只, 母鸡:", y, "只, 小鸡:", z, "只")
