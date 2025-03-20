public class MethodDetail02 {
    public static void main(String[] args) {
        int m1 = 3;
        double m2 = 6.3;
        String m3 = "草";
        Person person1 = new Person();
        // person1.eat1(m1);
        // person1.sleep(m2);
        Animals animals1 = new Animals();
        animals1.eat2(m3);
    }
}
// class Person{
//     String name;
//     int age;
//     public int eat1(int n1){
//         System.out.println("人类吃"+n1+"次饭");
//         return 1;
//     }
//     public double sleep(double n2){
//         System.out.println("人类睡"+n2+"小时觉");
//         return 2.1;
//     }
// }
class Animals{
    public String eat2(String n3){
        System.out.println("动物吃"+ n3);
        return " ";
    }
}
