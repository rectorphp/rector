var settings = {

	// PhpStorm
	// editor: '"C:\\Program Files\\JetBrains\\PhpStorm 2018.1.2\\bin\\phpstorm64.exe" --line %line% "%file%"',
	// title: 'PhpStorm',

	// NetBeans
	// editor: '"C:\\Program Files\\NetBeans 8.1\\bin\\netbeans.exe" "%file%:%line%" --console suppress',

	// Nusphere PHPEd
	// editor: '"C:\\Program Files\\NuSphere\\PhpED\\phped.exe" "%file%" --line=%line%',

	// SciTE
	// editor: '"C:\\Program Files\\SciTE\\scite.exe" "-open:%file%" -goto:%line%',

	// EmEditor
	// editor: '"C:\\Program Files\\EmEditor\\EmEditor.exe" "%file%" /l %line%',

	// PSPad Editor
	// editor: '"C:\\Program Files\\PSPad editor\\PSPad.exe" -%line% "%file%"',

	// gVim
	// editor: '"C:\\Program Files\\Vim\\vim73\\gvim.exe" "%file%" +%line%',

	// Sublime Text 2
	// editor: '"C:\\Program Files\\Sublime Text 2\\sublime_text.exe" "%file%:%line%"',

	// Visual Studio Code / VSCodium
	// editor: '"C:\\Program Files\\Microsoft VS Code\\Code.exe" --goto "%file%:%line%"',

	mappings: {
		// '/remotepath': '/localpath'
	}
};



if (!settings.editor) {
	WScript.Echo('Create variable "settings.editor" in ' + WScript.ScriptFullName);
	WScript.Quit();
}

var url = WScript.Arguments(0);
var match = /^editor:\/\/(open|create|fix)\/?\?file=([^&]+)&line=(\d+)(?:&search=([^&]*)&replace=([^&]*))?/.exec(url);
if (!match) {
	WScript.Echo('Unexpected URI ' + url);
	WScript.Quit();
}
for (var i in match) {
	match[i] = decodeURIComponent(match[i]).replace(/\+/g, ' ');
}

var action = match[1];
var file = match[2];
var line = match[3];
var search = match[4];
var replace = match[5];

var shell = new ActiveXObject('WScript.Shell');
var fileSystem = new ActiveXObject('Scripting.FileSystemObject');

for (var id in settings.mappings) {
	if (file.indexOf(id) === 0) {
		file = settings.mappings[id] + file.substr(id.length);
		break;
	}
}

if (action === 'create' && !fileSystem.FileExists(file)) {
	shell.Run('cmd /c mkdir "' + fileSystem.GetParentFolderName(file) + '"', 0, 1);
	fileSystem.CreateTextFile(file).Write(replace);

} else if (action === 'fix') {
	var lines = fileSystem.OpenTextFile(file).ReadAll().split('\n');
	lines[line-1] = lines[line-1].replace(search, replace);
	fileSystem.OpenTextFile(file, 2).Write(lines.join('\n'));
}

var command = settings.editor.replace(/%line%/, line).replace(/%file%/, file);
shell.Exec(command);

if (settings.title) {
	shell.AppActivate(settings.title);
}
