public class Homework05 {

	public static void main (String[] args) {

		/*
		随机生成10个整数(1_100的范围)保存到数组，
		并倒序打印以及求平均值、求最大值和最大值的下标、
		并查找里面是否有 8 
		 */
		
		int[] arr = new int[10];
		for(int i = 0; i < arr.length; i++) {
			arr[i] = (int)(Math.random() * 100) + 1; //生成 1-100的随机数作为数组的值
		}
		
		int max = arr[0];
		int index = 0;
		for(int i = 1; i < arr.length; i++) {
			if(max < arr[i]) {
				max = arr[i]; //记录最大值
				index = i;    //记录下标
			}
		}

		//通过 for 循环实现逆序赋值
		int[] arrTemp = new int[arr.length]; //创建临时数组
		int sum = 0;
		System.out.println("正序打印为:");
		for(int i = arr.length - 1,j = 0; i >= 0; i--,j++) {
			System.out.print(arr[j] + " ");
			sum += arr[j]; //记录总和以求平均值
			arrTemp[j] = arr[i];
		}
		arr = arrTemp; //地址拷贝

		//判断随机生成的数组里是否含 8,并将判断结果储存到b1中
		int num1 = 8;
		boolean b1 = false;
		for(int i = 0; i < arr.length; i++) {
			if(arr[i] == num1) {
				b1 = true;
				break;
			}
		}

		//输出结果
		System.out.println("\n倒序打印为:");
		for(int i = 0; i < arr.length; i++) {
			System.out.print(arr[i] + " ");
		}
		//冒泡排序法实现从小到大排序
		for(int j = 1; j < arr.length; j++) {
			//如果前面的数大于后面的数,就交换
			for(int i = 0; i < arr.length - j; i++) {
				if(arr[i] > arr[i + 1]) {
					int less = arr[i + 1];
					arr[i + 1] = arr[i];
					arr[i] = less;
				}
			}
		}
		
		System.out.println("\n从小到大打印为:");
		for(int i = 0; i < arr.length; i++) {
			System.out.print(arr[i] + " ");	
		}
		System.out.println("\n平均值为:" + sum / arr.length);
		System.out.println("最大值为:" + max + ",其下标(正序)为:" + index);
		System.out.println("平均值为:" + max / arr.length);
		if(b1) {
			System.out.println("数组含有8");
		} else {
			System.out.println("数组没有8");
		}
	}
}