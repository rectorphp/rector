#!/bin/bash
declare -A mapping

#
# Configure your editor by setting the $editor variable:
#

# Visual Studio Code
#editor='code --goto "$FILE":"$LINE"'
# Emacs
#editor='emacs +$LINE "$FILE"'
# gVim
#editor='gvim +$LINE "$FILE"'
# gEdit
#editor='gedit +$LINE "$FILE"'
# Pluma
#editor='pluma +$LINE "$FILE"'
# PHPStorm
# To enable PHPStorm command-line interface, folow this guide: https://www.jetbrains.com/help/phpstorm/working-with-the-ide-features-from-command-line.html
#editor='phpstorm --line $LINE "$FILE"'


#
# Optionally configure custom mapping here:
#

#mapping["/remotepath"]="/localpath"
#mapping["/mnt/d/"]="d:/"

#
# Please, do not modify the code below.
#

# Find and return URI parameter value. Or nothing, if the param is missing.
# Arguments: 1) URI, 2) Parameter name.
function get_param {
	echo "$1" | sed -n -r "s/.*$2=([^&]*).*/\1/ip"
}

if [[ -z "$editor" ]]; then
	echo "You need to set the \$editor variable in file '`realpath $0`'"
	exit
fi

url=$1
if [ "${url:0:9}" != "editor://" ]; then
	exit
fi

# Parse action and essential data from the URI.
regex='editor\:\/\/(open|create|fix)\/\?(.*)'
action=`echo $url | sed -r "s/$regex/\1/i"`
uri_params=`echo $url | sed -r "s/$regex/\2/i"`

file=`get_param $uri_params "file"`
line=`get_param $uri_params "line"`
search=`get_param $uri_params "search"`
replace=`get_param $uri_params "replace"`

# Debug?
#echo "action '$action'"
#echo "file '$file'"
#echo "line '$line'"
#echo "search '$search'"
#echo "replace '$replace'"

# Convert URI encoded codes to normal characters (e.g. '%2F' => '/').
printf -v file "${file//%/\\x}"
# And escape double-quotes.
file=${file//\"/\\\"}

# Apply custom mapping conversion.
for path in "${!mapping[@]}"; do
	file="${file//$path/${mapping[$path]}}"
done

# Action: Create a file (only if it does not already exist).
if [ "$action" == "create" ] && [[ ! -f "$file" ]]; then
	mkdir -p $(dirname "$file")
	touch "$file"
fi

# Action: Fix the file (if the file exists and while creating backup beforehand).
if [ "$action" == "fix" ]; then

	if [[ ! -f "$file" ]]; then
		echo "Cannot fix non-existing file '$file'"
		exit
	fi

	# Backup the original file.
	cp $file "$file.bak"
	# Search and replace in place - only on the specified line.
	sed -i "${line}s/${search}/${replace}/" $file

fi

# Format the command according to the selected editor.
command="${editor//\$FILE/$file}"
command="${command//\$LINE/$line}"

# Debug?
#echo $command

eval $command
