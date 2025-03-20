public class Properties {//属性
    public static void main(String[] args) {
        Person p1 = new Person();
        System.out.println("\n当前这个人的信息");
        System.out.println("年龄：" + p1.age);
        System.out.println("体重：" + p1.weight);
        System.out.println("姓名：" + p1.name);
        System.out.println("是否为男性:" + p1.isMale);
    }
}

class Person {
    int age;
    double weight;
    String name;
    boolean isMale;

}