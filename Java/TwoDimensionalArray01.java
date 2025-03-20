public class TwoDimensionalArray01 {
        public static void main(String[] args){
        int[][] arr = {{8,9,6,5,4,7},
                    {8,9,6,5,4,7},
                    {8,9,6,5,4,7},
                     {8,9,6,5,4,7}, 
                     {8,9,6,5,4,7},
                     {8,9,6,5,4,7}};
                     
                     System.out.println("Array:");
                     
     for(int i = 0; i < arr.length; i++) {
        for(int j = 0; j < arr[i].length; j++) {
            System.out.print(arr[i][j] + " ");
        }
        System.out.println();
    } 
}
}