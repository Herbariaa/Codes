public class ThisHomeWork02 {
    public static void main(String[] args){
       String[] strs = {"jack", "mary", "tom", "lim", "duke"};
       A02 a02 = new A02();
       int index = a02.find("tom", strs);
       if(index != -1){
            System.out.println("已经找到该名字, 其序号为: " + (index + 1));
       }else{
            System.out.println("没有找到该名字");
       }
    }
}
class A02{
    public int find(String findStr, String[] strs){
        for(int i = 0; i < strs.length; i++){
            if(findStr.equals(strs[i])){
                return i;
            }
        }
        return -1;
    }
}