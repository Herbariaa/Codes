# 计算斐波那契数列的前 10 个数字
def fibonacci(n):
    if n <= 0:
        return []
    elif n == 1:
        return [0]
    elif n == 2:
        return [0, 1]
    else:
        fib = [0, 1]
        for _ in range(2, n):
            fib.append(fib[-1] + fib[-2])
        return fib

# 打印前 10 个斐波那契数
result = fibonacci(10)
print(result)