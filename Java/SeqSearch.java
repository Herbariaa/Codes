import java.util.Scanner;
public class SeqSearch {
    public static void main(String[] args){
        /*
         1.定义字符串数组
         2.接收输入,遍历数组,逐一比较
         */
      String[] names = {"永","除","塔","非"};
      Scanner myScanner = new Scanner(System.in);
      System.out.println("请输入名字");
      String findName = myScanner.next();
      //遍历数组,逐一比较
      int index = -1;
      for (int i = 0; i < names.length; i++) {
        if (findName.equals(names[i])) {
            System.out.println("找到了" + findName);
            index = i;
            break;
        } 
    }
        if (index == -1) {
             System.out.println("没有找到" + findName);
        }
    }
}