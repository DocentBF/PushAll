#!/bin/bash

repl1=PushAll
repl2=pushall

find1=modPushAll
find2=modpushall
path=./modPushAll

repl3=$repl1"ManagerController"

cd ..
for i in {1..10}
do
    find $path -name \*$find2\* -a ! -name rename_it | xargs perl -e 'for(@ARGV) { $a=$_; s/'$find2'/'$repl2'/g; rename $a,$_; print "$_\n" }'
done

for i in `egrep -r -i $find1 $path/_build $path/core $path/assets | grep -v svn | cut -d ":" -f1`
do
    reg="s/$find1/$repl1/g"
    sed -e $reg $i > ${i}.bak
    mv ${i}.bak $i

    reg="s/$find2/$repl2/g"
    sed -e $reg $i > ${i}.bak
    mv ${i}.bak $i

    reg="s/$repl3/modPushAllManagerController/g"
    sed -e $reg $i > ${i}.bak
    mv ${i}.bak $i

    echo $i
done



mv $find1 $repl1
