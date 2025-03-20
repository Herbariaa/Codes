public class ThisHomeWork01 {
    public static void main(String[] args){
        A01 a01 = new A01();
        double[] arr = {-2.33, 5.4, 6.7, 84.5, 75.4};
        Double res = a01.max(arr);
        if(res != null){
            System.out.println("arr01的最大值 = " + a01.max(arr));
        }else{
            System.out.println("输入的值有误!");
        }
    }
}
/*
类名A01,方法名max,形参double[],返回值double
 */
class A01{
    public Double max(double[] arr){
        if( arr!= null && arr.length > 0){
            double max = arr[0];
            for(int i = 1; i < arr.length; i++){
                if(arr[i] > max){
                    max = arr[i];
                }
            }
            return max;
        }else{
            return null;
        }
        
    }
}
