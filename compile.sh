#!/bin/bash
for PROGRAM in *.asm; do
	yasm -f elf64 $PROGRAM && ld ${PROGRAM/%.asm/.o} -o bench_${PROGRAM%%.asm}
	rm ${PROGRAM/%.asm/.o}
done

for PROGRAM in *.c; do
	gcc -std=c99 $PROGRAM -o bench_${PROGRAM%%.c}
done
