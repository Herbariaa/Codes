public class Random01 {
    public static void main(String[] args){
        int arr[] = new int[10];
        //生成随机数
        for(int i = 0; i < arr.length; i++){
            arr[i] = (int)(Math.random() * 100) + 1;
        }
        //
        System.out.println("随机数为(正序)：");
        for(int i = 0 ; i < arr.length;  i++){
            System.out.print(arr[i] + " ");
        }
        //
        System.out.println();
        System.out.println("随机数为(倒序)：");
        for(int i = arr.length - 1 ; i >= 0;  i--){
            System.out.print(arr[i] + " ");
        }
        System.out.println();
        //
        int arrMax = 0;
        double sum = 0;
        int indexArray = 0;
        for(int i = 0; i < arr.length; i++){
            if(arr[i] > arrMax){
                arrMax = arr[i];
                indexArray = i;
            }
            sum += arr[i];
        }
        System.out.println("平均数为"+sum/arr.length+"\n"+"最大值为"+
        arrMax+"\n"+"最大值下标是"+indexArray);
        //查找数组中是否有8
        int findNum = 8;
        int index = -1;
        for(int i = 0; i < arr.length; i++){
            if(findNum == arr[i]){
                System.out.println("找到数" + findNum + "下标=" + i);
                index = i;
                break;
            }
        }
        if(index == -1){
            System.out.println("没有找到数" + findNum);
        }
    }
}
