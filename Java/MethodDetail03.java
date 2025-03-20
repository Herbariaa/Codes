public class MethodDetail03 {
    public static void main(String[] args) {
        A a = new A();
        a.sayOk();
        a.m1();
    }
}
class A{
    public void print(int n){
        System.out.println("print方法被调用 n = " + n);
    }
    public void sayOk(){//同一个类中直接调用其他的方法
        print(10);
        System.out.println("继续执行sayOK~~~");
    }
    public void m1(){
        System.out.println("m1方法被调用");//1
        B b = new B();
        b.hi();
        System.out.println("m1()继续执行");//3
    }
}
class B{
    public void hi(){
        System.out.println("B类中的hi()被执行");//2
    }  
}
