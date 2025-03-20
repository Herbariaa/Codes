public class Method01 {
    //编写一个main方法;
    public static void main(String[] args){
        Person p1 = new Person();//先创建对象,再调用方法
        p1.speak();
        // p1.cal01();
    }
}
class Person {//创建类
    String name;
    int age;
    //方法(成员方法)
    //添加speak成员方法,输出字符串
    void speak(){
        System.out.println("我是一个正常人");
    }
    // public void cal01(){
    //     for(int i = 2; i < 10; i++){
    //         System.out.println(i % 2);
    //     }
    // }

}
