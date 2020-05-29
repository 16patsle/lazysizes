// https://www.geeksforgeeks.org/print-all-possible-combinations-of-r-elements-in-a-given-array-of-size-n/
// Program to print all combination of size r in an array of size n

// The main function that prints all combinations of size r inarr[] of size n.
// This function mainly uses combinationUtil()
function getCombination(arr, n, r) {
	// A temporary array to store all combination one by one
	const data = [];

	// Stores results
	const results = [];

	// Print all combination using temporary array 'data[]'
	combinationUtil(results, arr, data, 0,
	n - 1, 0, r);

	return results;
}

/*
arr[] ---> Input Array
data[] ---> Temporary array to
store current combination
start & end ---> Staring and Ending indexes in arr[]
index ---> Current index in data[]
r ---> Size of a combination to be printed
*/
function combinationUtil(results, arr, data, start, end, index, r) {
	// Current combination is ready to be printed, print it
	if (index == r) {
		const result = [];
		for (let j = 0; j < r; j++){
			result.push(data[j]);
		}
		results.push(result);
		return;
	}

	// replace index with all possible elements.
	// The condition "end-i+1 >= r-index" makes sure that including one element at
	// index will make a combination with remaining elements at remaining positions
	for (let i = start; i <= end && end - i + 1 >= r - index; i++){
		data[index] = arr[i];
		combinationUtil(results, arr, data, i + 1, end, index + 1, r);
	}
}

module.exports = getCombination;
