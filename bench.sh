#!/bin/bash
truncate --size 0 results.log
echo "CPU$(head -n 5 /proc/cpuinfo | tail -n 1 | cut -d ':' -f 2)" >> results.log
uname -a  >> results.log
echo >> results.log

TIMEFORMAT="real %R user %U system %S"
for PROGRAM in bench_*; do
	echo "Running $PROGRAM..."
	truncate --size 0 bench.log
	
	for i in {1..10}; do
		(time ./$PROGRAM) 2>> bench.log
	done
	
	echo $PROGRAM >> results.log
	awk '
		    { r += $2; u += $4; s += $6; }
		END { printf("average real %.3f user %.3f system %.3f\n", r / NR, u / NR, s / NR); }
	' bench.log  >> results.log
	cat bench.log >> results.log
done

echo >> results.log
cat /proc/cpuinfo >> results.log

rm -f bench.log