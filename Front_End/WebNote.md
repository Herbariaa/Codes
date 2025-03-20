# HTML
## 概况
```dotnetcli
'HTML'全称是'Hypertext Markup Language'
即超文本标记语言
```
通过一系列'标签(也称为元素)'来定义文本/图像/链接等等.
HTML标签是由尖括号包围的关键字.
标签通常成对出现,包括开始标签和结束标签(也称为双标签),
内容位于这两个标签之间,例如:
```dotnetcli
<p>这是一个段落</p>
<h1>一级标题标签</h1>
<a herf="#">这是一个超链接</a>
```
除了双标签,也存在单标签,例如:
```dotnetcli
<input type="text">
<br>换行
<hr>分割线
```
区别:单标签用于没有内容的元素,双标签适用于有内容的元素.

## HTML文件结构
```dotnetcli
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.常见文本标签</title>
</head>
<body>
  <h1>一级标题标签</h1>   
  <h2>二级标题标签</h2> 
  <h3>三级标题标签</h3> 
  <h4>四级标题标签</h4> 
  <h5>五级标题标签</h5> 
  <h6>六级标题标签</h6> 
  <h7>七级标题标签(无效)</h7> 
  <hr>
  <p>这是一个段落标签(br换行,hr分割)<br>
    <b>文本加粗</b>,<br>
    <i>文本斜体</i>,<br>
    <s>删除线</s>
  </p>
  <hr>
  <ul>无序列表用ul
    <li>无序列表元素1</li>
    <li>无序列表元素2</li>
    <li>无序列表元素3</li>
    <li>无序列表元素4</li>    
    <li>无序列表元素5</li>
    <li>无序列表元素6</li>
  </ul>
  <ol>有序列表用ol
    <li>有序列表元素1</li>
    <li>有序列表元素2</li>
    <li>有序列表元素3</li>
    <li>有序列表元素4</li>
    <li>有序列表元素5</li>
  </ol>
  <hr>
  <h4>table row(也就是tr,列表的行标签)</h4>
  <h4>table data(也就是td,列表中的表格数据)</h4>
  <h4>table header(也就是th,写在第一个行标签中作表头)</h4>
  <table border="2">
    (border就是table标签的一个属性)
    <tr>
        <th>列标题1</th>
        <th>列标题2</th>
        <th>列标题3</th>
        <th>列标题4</th>
        <th>列标题5</th>
        <th>列标题6</th>
    </tr>
    <tr>
        <td>element11</td>
        <td>element12</td>
        <td>element13</td>
        <td>element14</td>
        <td>element15</td>
        <td>element16</td>
    </tr>
    <tr>
      <td>element11</td>
      <td>element12</td>
      <td>element13</td>
      <td>element14</td>
      <td>element15</td>
      <td>element16</td>
    </tr>  
    <tr>
      <td>element11</td>
      <td>element12</td>
      <td>element13</td>
      <td>element14</td>
      <td>element15</td>
      <td>element16</td>
    </tr>
  </table>
<hr>
</body>
</html>
```
## HTML属性
属性在HTML中定义元素的行为和外观,以及与其他元素之间的关系

基本语法:
```dotnetcli
<开始标签 属性名="属性值">
属性名用来标识属性,属性值定义属性的值
```
每个HTML元素可以具有不同的属性
```dotnetcli
<p id="describe" class="section">这是一个段落标签</p>
<a herf="https://www.baidu.com">这是一个超链接</a>
```
属性名不区分大小写,属性值对大小写敏感
```dotnetcli
<img src="exam.jpg" alt=" ">
<img SRC="exam.jpg" alt=" ">
<img src="EXAM.JPG" alt=" ">
<!-- 前两者相同,第三个与前两个不一样 -->
```

## 适用于大多数HTML元素的属性
|属性|描述|
|:----:|:----:|
|class|为HTML元素定义一个或多个类名(类名从样式文件引入)|
|id|定义元素唯一的id|
|style|规定元素的行内样式|

例如:
```dotnetcli
<h1 id="title"></h1>
<div class="nav-bar"></div>
<h2 class="nav-bar"></h2>
```
下面是练习:
2.HTML属性.html
```dotnetcli
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.html属性</title>
</head>
<body>
    <div>a标签(常用于): herf定义链接,target定义链接打开方式</div>
    <a href="https://www.baidu.com" target="_top">文本:这是一个超链接</a><br>
    <a href="https://www.baidu.com" target="_self">文本:这是一个超链接</a><br>
    <a href="https://www.baidu.com" target="_blank">文本:这是一个超链接</a><br>
    <a href="https://www.baidu.com" target="_parent">文本:这是一个超链接</a><br>
    <br>
    <a href="https://docs.geekmans.com">zhes1</a>
    <hr>
    <div>src标签: 定义图像的路径/url等  alt标签: 定义图像的替代文本</div>
    <img src="39fca3a7b92db2d908c77d6fb8c598c7.jpg" alt="定义图像的替代文本,若图像无法正常显示,则显示该文本" width="200" height="100">
    <img src="https://image.baidu.com/search/dook" alt="该图片暂时无法加载">
</body>
</html>
```


## HTML区块-块元素与行内元素
### 块元素(block)
用于组织和布局页面的主要结构和内容,例如段落,标题,表格,列表等等.它们用于创建页面的主要部分,将内容分割为逻辑块.
1. 通常从新行开始,并占据整行宽度,在页面上呈现为一块独立的内容块.
2. 可包含其他块级元素和行内元素
3. 常见的块级元素包括
```<div>,<p>,<h1>到<h6>,<ul>,<ol>,<li>,<table>,<form>等```
```<div>```标签常用于创建块级容器,以便于组织页面的结构和布局(通常与CSS和JS联合使用,以实现更加复杂的页面布局)

### 行内元素
行内元素通常用于添加文本样式或为文本中的一部分应用样式.它们可以在文本中插入小的元素,例如超链接,强调文本等.
1. 行内元素通常在同一行内呈现,不会独占一行.
2. 宽度只占据内容所需,而非整行
3. 行内元素不能包含块级元素,但可以包含其他行内元素
4. 常见的行内元素包括```<span>,<a>,<strong>(加粗),<em>(斜体),<img>,<br>(换行),<input>(输入)等```
```<span>```标签常用于内联样式化文本,给文本的一部分应用样式或标记(通常与CSS和JS联合使用,以实现更加复杂的页面布局)
下面是练习:
3.HTML区块.html
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="nav bar">
        <a href="a">链接1</a>
        <a href="a">链接2</a>
        <a href="a">链接3</a>
        <a href="a">链接4</a>
        <a href="a">链接5</a>
    </div>
    <div class="content">
        <h1>
            文章标题
        </h1>
        <p>文章内容</p>
        <p>文章内容</p>
        <p>文章内容</p>
        <p>文章内容</p>
        <p>文章内容</p>
    </div>
    <span>第一个span标签</span>
    <span>第一个span标签</span>
    <span>第一个span标签</span>
    <span>第一个span标签</span>
    <span>第一个span标签</span>
    <div id="nav">

    </div>

</body>
</html>
```
## 表单
4.HTML表单.html
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="">
        <label for="username">用户名:</label>
        <input type="text" id="username" placeholder="请输入用户名"><br><br>

        <label for="password">密码:</label>
        <input type="password" id="password" placeholder="请输入密码"><br><br>

        <label>性别</label>
        <input type="radio" name="gender"> 男
        <input type="radio" name="gender"> 女
        <input type="radio" name="gender"> 保密
        <br><br>

        <label>爱好</label>
        <input type="checkbox" name="hobby"> 唱
        <input type="checkbox" name="hobby"> 跳
        <input type="checkbox" name="hobby"> rap
        <input type="checkbox" name="hobby"> 打篮球
        <br><br>

        <input type="submit" value="上传">
    </form>

    <form action="#"></form>
</body>
</html>
```


# CSS

CSS全名是```Cascading Style Sheets```,中文名```层叠样式表```

用于定义网页样式和布局的样式表语言.

通过CSS,你可以指定页面中各个元素的颜色,字体,大小,间距,边框,背景等样式,从而实现更精确的页面设计.

HTML相当于房子的骨架(有什么),CSS样式相当于装修(长什么样子).

## CSS语法

CSS通常由选择器,属性和属性值组成,多个规则可以组合在一起,以便同时应用多个样式
```dotnetcli
选择器{
    属性1: 属性值1;
    属性2: 属性值2;
}
```
1. 选择器的声明中可以写无数条属性
2. 声明的每一行属性,都需要以英文分号结尾
3. 声明中的所有属性和值都是以键值对的形式出现的

实例
```dotnetcli
/*这是一个p标签选择器*/
p{
    color: blue;
    font-size: 16px;
}
```
练习:
5.CSS导入方式.html
```dotnetcli
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS导入方式</title>

    <style>
        p{
            color: blue;
            font-size: 100px;
        }
    </style>
</head>
<body>
    <p>这是一个应用了CSS样式的文本</p>
</body>
</html>
```

## CSS三种导入方式
下面是三种常见的CSS导入方式:
1. ```内联样式(Inline Styles)```
2. ```内部样式表(Internal Stylesheet)```
3. ```外部样式表(External Stylesheet)```
  
三种导入方式的优先级: 内联样式>内部样式表>外部样式表


练习
```dotnetcli
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS导入方式</title>
    <link rel="stylesheet" href="./CSS/styles.css">
    <style>
        p{
            color: blue;
            font-size: 100px;
        }
        h2{
            color: green;
        }
    </style>
</head>
<body>
    <p>这是一个应用了CSS样式的文本</p>
    <h1 style="color: red;">这是一个一级标签,使用内联样式</h1>
    <h2>这是一个二级标签,使用内部样式表</h2>
    <h3>这是一个三级标签,使用外部样式表</h3>
</body>
</html>
```

## 选择器

选择器是CSS中的关键部分,它允许你针对特定元素或一组元素定义样式

1. 元素选择器(标签选择器)
2. 类选择器
3. ID选择器
4. 通用选择器
5. 子元素选择器
6. 后代选择器(包含选择器)
7. 并集选择器(兄弟选择器)
8. 伪类选择器
  
```dotnetcli
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS选择器</title>
    <style>
        /* 元素选择器 */
        h2{
            color: aqua;
        }
        /* 类选择器 */
        .highlight{
            background-color: yellow;
        }
        /* ID选择器 */
        #header{
            font-size: 100px;
        }
        /* 通用选择器 */
        *{
            font-family: 'KaiTi';
            font-weight: bolder;
        }
        /* 子元素选择器 */
        .father > .son {
            color: yellowgreen;
        }
        /* 后代选择器 */
        .father p{
            color: brown;
            font-size: larger;
        }
        /* 相邻元素选择器 */
        h3 + p {
            background-color: red;
        }
        /* 伪类选择器 */
        #element:hover{
            background-color: purple;
        }
        /* 
            选中第一个子元素 :first-child
                 n       :nth-child()
                          :active
         */

        /* 
            伪元素选择器
            ::after
            ::before
        */
    </style>
</head>
<body>
    <h1>不同类型的CSS选择器</h1>

    <h2>这是一个元素选择器示例</h2>

    <h3 class="highlight">这是一个类选择器示例</h3>

    <h3>这是另一个类选择器示例</h3>

    <h4 id="header">这是一个ID选择器</h4>

    <div class="father">
        <p class="son">这是一个子元素选择器示例</p>
        <div>
            <p class="grandson">这是一个后代选择器示例</p>
        </div>
    </div>
    <p>这是一个普通的p标签</p>
    <h3>这是一个相邻兄弟选择器示例</h3>
    <p>这是另一个普通的p标签</p>

    <h3 id="element">这是一个伪类选择器示例</h3>
</body>
</html>
```

## CSS常用属性


# ajax
# nodejs
# vue2、3




