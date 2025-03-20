public class insertNum {
    public static void main(String args[]){
        //数组扩容+定位
        //1.先确定添加数插入到哪个索引
        //2.扩容
        int[] arr = {10,13,45,90};
        int insertnum = 23;
        int index = -1;
        //遍历arr数组,如果发现insertnum<=arr[i],说明i就是要插入的位置
        //使用index保留index = i;
        for(int i = 0; i < arr.length; i++){
            if(insertnum <= arr[i]){
                index = i;
                break;
            }
        }
        if(index == -1){
            index = arr.length;
        }
        System.out.println(index);
        int[] arrnew = new int[arr.length + 1];
        for(int i = 0, j = 0; i < arrnew.length; i++){
            if(i != index){//可以把arr的元素拷贝到arrnew
                arrnew[i] = arr[j];
                j++;/*
                i != index; 则说明不是要插入的位置, 
                这个位置应该使用arr[j]来给arrnew[i]赋值
                所以j赋完值之后自增
                */
            }else{
                arrnew[i] = insertnum;
            }
        }
        for(int i = 0; i < arrnew.length; i++){ 
            System.out.print(arrnew[i] + "\t");
        }
    }
}
