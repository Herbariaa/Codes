import os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# 设置Edge WebDriver路径
edge_driver_path = '/Users/a1234/Downloads/edgedriver_mac64_m1/msedgedriver'
# 指定文件上传的目录
directory = '/Users/a1234/Documents/转化基金项目上传-整理后/单文件项目'

# 创建Edge WebDriver实例
options = webdriver.EdgeOptions()
options.binary_location = edge_driver_path
driver = webdriver.Edge(options=options)

# 打开网页
driver.get('https://capital.siicshc.com/?/bp_shaccompany/add')

# 等待页面加载
wait = WebDriverWait(driver, 10)

# 遍历指定目录中的所有文件
for filename in os.listdir(directory):
    # 构建文件上传路径
    file_path = os.path.join(directory, filename)
    
    # 检查文件是否存在
    if os.path.exists(file_path):
        # 查找并点击上传按钮
        upload_button = driver.find_element(By.ID, 'syjhsfile213_file')
        upload_button.click()
        
        # 等待文件选择对话框出现
        wait.until(EC.presence_of_element_located((By.ID, 'file-input')))
        
        # 选择文件
        driver.find_element(By.ID, 'file-input').send_keys(file_path)
        
        # 提交表单
        driver.find_element(By.ID, 'submit').click()
        
        # 等待上传完成
        wait.until(EC.presence_of_element_located((By.ID, 'success')))
        
        # 检查上传是否成功
        success_message = driver.find_element(By.ID, 'success').text
        if success_message == '上传成功':
            print(f'文件 {filename} 上传成功. 😄')
        else:
            print(f'文件 {filename} 上传失败. 状态: {success_message} 😞')
    else:
        print(f'文件 {filename} 不存在，跳过上传. 🚫')

# 关闭浏览器
driver.quit()