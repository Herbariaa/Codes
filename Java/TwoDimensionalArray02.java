public class TwoDimensionalArray02 {
    public static void main(String[] args){
        int arr[][] = {
            {4,6},{1,4,5,7},{2,3,6,9},{1,2,3,4,6}
        };
        int sum = 0;
        for(int i = 0; i < arr.length; i++){
            for(int j = 0; j < arr[i].length; j++){
                System.out.print(arr[i][j]+" ");
                sum += arr[i][j];
                
            }System.out.println( );
        }System.out.println(sum);

    }
}
