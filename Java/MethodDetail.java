public class MethodDetail {
    public static void main(String[] args){
        byte z1 = 8;
        byte z2 = 4;
        AA arr = new AA();
        int[] resArr = arr.getSumAndSub(z1,z2);//z1 and z2 实际参数
        System.out.println("和= "+resArr[0] + "\n差= " + resArr[1]);
        double sum1 = arr.f1(8, 4);
        int sub1 = arr.f2(8, 4);
        System.out.println("和= "+sum1 + "\n差= " + sub1);
        double out1 = arr.f3(8, 4);
        System.out.println(out1);
        //void 可不写return 语句, 也可只写return
        return;
    } 
}
class AA{//方法不能嵌套定义,在一个类里面平行定义多个方法
    public int[] getSumAndSub(int a, int b){
        //a, b 为形式参数,与实际参数类型相同或兼容
        //一个方法最多一个返回值,使用数组返回多个数值
        //返回类型(int [])可以为基本数据类型或引用类型
        int[] resArr = new int [2];
        resArr[0] = a + b;
        resArr[1] = a - b;
        return resArr;//若public 后不是 void, 必须要有return
    }
    public double f1(int a, int b){
        return a + b;
    }
    public int f2(int a, int b){
        return a - b;
    }
    public double f3(int a, int b){
        System.out.println("f3");
        //void 可不写return 语句, 也可只写return
        return a*b;
    }
}
