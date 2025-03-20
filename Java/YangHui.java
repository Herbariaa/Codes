public class YangHui {
    public static void main(String[] args) {
        int[][] YangHui  = new int [13][];
        for (int i = 0; i < YangHui.length; i++) {
            YangHui[i] = new int[i+1];//给数组的第i行开辟空间
            for (int j = 0; j < YangHui[i].length; j++) {
                if(j == 0 || j == YangHui[i].length - 1){
                    YangHui [i][j] = 1;
                }
                else{
                    YangHui [i][j] = YangHui[i-1][j-1] + YangHui[i-1][j];
                }
            }
        }
        for(int i = 0; i < YangHui.length; i++){
            for(int j = 0; j < YangHui[i].length; j++){
                System.out.print(YangHui[i][j] + " \t");
            }
            System.out.println();
        }
    }
}
