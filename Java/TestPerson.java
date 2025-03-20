public class TestPerson {
    public static void main(String[] args){
        Person p1 = new Person("mary", 20);
        Person p2 = new Person("smith", 30);

        System.out.println("p1和p2比较的结果如下:" + p1.compareTo(p2));

    }
}
class Person{
    String name;
    int age;
    public Person(String name, int age){
        this.name = name;
        this.age = age;
    }
    public boolean compareTo(Person p){
        // if(this.name.equals(p.name) && this.age == p.age){
        //     return true;
        // }else{
        //     return false;
        // }  可以简化写成:
        return this.name.equals(p.name) && this.age == p.age;
        //即这个return返回的值是boolean值时,就可直接return到条件表达式;
    
    }
}

