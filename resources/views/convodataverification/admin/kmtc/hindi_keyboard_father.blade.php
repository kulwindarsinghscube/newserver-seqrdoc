<script language=javascript type="text/javascript">

	var IsValid = false;
	var IsCaps = false;
	var IsShift_f = false;

	var VirtualKey = {
		'113': 'ौ', '119': 'ै', '101': 'ा', '114': 'ी', '116': 'ू', '121': 'ब', '117': 'ह', '105': 'ग', '111': 'द', '112': 'ज',
		'97': 'ो', '115': 'े', '100': '्', '102': 'ि', '103': 'ु', '104': 'प', '106': 'र', '107': 'क', '108': 'त',
		'122': '', '120': 'ं', '99': 'म', '118': 'न', '98': 'व', '110': 'ल', '109': 'स',
		'81': 'औ', '87': 'ऐ', '69': 'आ', '82': 'ई', '84': 'ऊ', '89': 'भ', '85': 'ङ', '73': 'घ', '79': 'ध', '80': 'झ',
		'65': 'ओ', '83': 'ए', '68': 'अ', '70': 'इ', '71': 'उ', '72': 'फ', '74': 'ऱ', '75': 'ख', '76': 'थ',
		'90': '', '88': 'ँ', '67': 'ण', '86': '', '66': '', '78': 'ळ', '77': 'श',
		'96': '`', '49': '1', '50': '2', '51': '3', '52': '4', '53': '5', '54': '6', '55': '7', '56': '8', '57': '9', '48': '0', '45': '-', '61': 'ृ', '92': 'ॉ',
		'91': 'ड', '93': '़',
		'59': 'च', '39': 'ट',
		'44': ',', '46': '.', '47': 'य',
		'126': '', '33': 'ऍ', '64': 'ॅ', '35': '्', '36': 'र्', '37': 'ज्ञ', '94': 'त्र', '38': 'क्ष', '42': 'श्र', '40': '(', '41': ')', '95': 'ः', '43': 'ॠ', '124': 'ऑ',
		'123': 'ढ', '125': 'ञ',
		'58': 'छ', '34': 'ठ',
		'60': 'ष', '62': '।', '63': 'य़',
		'32': ' '
	};

	var VirtualKeyCaps = {
		'113': 'ौ', '119': 'ै', '101': 'ा', '114': 'ी', '116': 'ू', '121': 'ब', '117': 'ह', '105': 'ग', '111': 'द', '112': 'ज',
		'97': 'ो', '115': 'े', '100': '्', '102': 'ि', '103': 'ु', '104': 'प', '106': 'र', '107': 'क', '108': 'त',
		'122': '', '120': 'ं', '99': 'म', '118': 'न', '98': 'व', '110': 'ल', '109': 'स',
		'81': 'औ', '87': 'ऐ', '69': 'आ', '82': 'ई', '84': 'ऊ', '89': 'भ', '85': 'ङ', '73': 'घ', '79': 'ध', '80': 'झ',
		'65': 'ओ', '83': 'ए', '68': 'अ', '70': 'इ', '71': 'उ', '72': 'फ', '74': 'ऱ', '75': 'ख', '76': 'थ',
		'90': '', '88': 'ँ', '67': 'ण', '86': '', '66': '', '78': 'ळ', '77': 'श',
		'96': '', '49': 'ऍ', '50': 'ॅ', '51': '्', '52': 'र्', '53': 'ज्ञ', '54': 'त्र', '55': 'क्ष', '56': 'श्र', '57': '(', '48': ')', '45': 'ः', '61': 'ॠ', '92': 'ऑ',
		'91': 'ढ', '93': 'ञ',
		'59': 'छ', '39': 'ठ',
		'44': 'ष', '46': '।', '47': 'य़',
		'126': '', '33': '1', '64': '2', '35': '3', '36': '4', '37': '5', '94': '6', '38': '7', '42': '8', '40': '9', '41': '0', '95': '-', '43': 'ृ', '124': 'ॉ',
		'123': 'ड', '125': '़',
		'58': 'च', '34': 'ट',
		'60': ',', '62': '.', '63': 'य',
		'32': ' '
	};

	var LeftButton = {
		'81': '31', '87': '51', '69': '71', '82': '91', '84': '111', '89': '131', '85': '151', '73': '171', '79': '191', '80': '211',
		'65': '37', '83': '57', '68': '77', '70': '97', '71': '117', '72': '137', '74': '157', '75': '177', '76': '197',
		'90': '47', '88': '67', '67': '87', '86': '107', '66': '127', '78': '147', '77': '167',
		'96': '0', '49': '20', '50': '40', '51': '60', '52': '80', '53': '100', '54': '120', '55': '140', '56': '160', '57': '180', '48': '200', '189': '220', '187': '240', '220': '260',
		'192': '0', '33': '20', '64': '40', '35': '60', '36': '80', '37': '100', '94': '120', '38': '140', '42': '160', '40': '180', '41': '200', '95': '220', '43': '240', '124': '260',
		'219': '231', '221': '251',
		'186': '217', '222': '237',
		'188': '187', '190': '207', '191': '227',
		'32': ' '
	};

	var TopButton = {
		'81': '20', '87': '20', '69': '20', '82': '20', '84': '20', '89': '20', '85': '20', '73': '20', '79': '20', '80': '20',
		'65': '40', '83': '40', '68': '40', '70': '40', '71': '40', '72': '40', '74': '40', '75': '40', '76': '40',
		'90': '60', '88': '60', '67': '60', '86': '60', '66': '60', '78': '60', '77': '60',
		'96': '0', '49': '0', '50': '0', '51': '0', '52': '0', '53': '0', '54': '0', '55': '0', '56': '0', '57': '0', '48': '0', '189': '0', '187': '0', '220': '0',
		'192': '0', '33': '0', '64': '0', '35': '0', '36': '0', '37': '0', '94': '0', '38': '0', '42': '0', '40': '0', '41': '0', '95': '0', '43': '0', '124': '0',
		'219': '20', '221': '20',
		'186': '40', '222': '40',
		'188': '60', '190': '60', '191': '60',
		'32': ' '
	};

	var ValidButton = {
		'81': '1', '87': '1', '69': '1', '82': '1', '84': '1', '89': '1', '85': '1', '73': '1', '79': '1', '80': '1',
		'65': '1', '83': '1', '68': '1', '70': '1', '71': '1', '72': '1', '74': '1', '75': '1', '76': '1',
		'90': '1', '88': '1', '67': '1', '86': '1', '66': '1', '78': '1', '77': '1',
		'96': '0', '49': '0', '50': '0', '51': '0', '52': '0', '53': '0', '54': '0', '55': '0', '56': '0', '57': '0', '48': '0', '189': '0', '187': '0', '220': '0',
		'192': '0', '33': '0', '64': '0', '35': '0', '36': '0', '37': '0', '94': '0', '38': '0', '42': '0', '40': '0', '41': '0', '95': '0', '43': '0', '124': '0',
		'219': '1', '221': '1',
		'186': '1', '222': '1',
		'188': '1', '190': '1', '191': '1',
		'32': ' ',
		'8': '1', '9': '1', '13': '1', '16': '1', '20': '1', '46': '1'
	};

	function checkCode_f(evt) {
		var kcode = 0;

		if (document.all) {
			var evt = window.event;
			kcode = evt.keyCode;
		}
		else kcode = evt.which;

		if (ValidButton[kcode]) { ButtonDown_f(kcode); IsValid = true; } else { IsValid = false; };
	}

	function reset_f() {
		if (!IsCaps && !IsShift_f) {
			document.getElementById('normal_f').style.visibility = "visible";
			document.getElementById('Shift_c_f').style.visibility = "hidden";
			document.getElementById('caps_f').style.visibility = "hidden";
		}
		else if (IsCaps && !IsShift_f) {
			document.getElementById('normal_f').style.visibility = "hidden";
			document.getElementById('Shift_c_f').style.visibility = "hidden";
			document.getElementById('caps_f').style.visibility = "visible";

			document.getElementById('capsS_f').style.visibility = "visible";
		}
		else if (!IsCaps && IsShift_f) {
			document.getElementById('normal_f').style.visibility = "hidden";
			document.getElementById('Shift_c_f').style.visibility = "visible";
			document.getElementById('caps_f').style.visibility = "hidden";
		}
		else if (IsCaps && IsShift_f) {
			
			document.getElementById('normal_f').style.visibility = "visible";
			document.getElementById('Shift_c_f').style.visibility = "hidden";
			document.getElementById('caps_f').style.visibility = "hidden";

			document.getElementById('capsS_f').style.visibility = "visible";
		}
	}

	function restoreCode_f(evt) {
		var kcode = 0;

		if (document.all) {
			var evt = window.event;
			kcode = evt.keyCode;
		}
		else kcode = evt.which;

		ButtonUp_f(kcode);
	}

	function writeKeyPressed_f(evt) {
		var kcode = 0;

		if (document.all) {
			var evt = window.event;
			kcode = evt.keyCode;
		}
		else kcode = evt.which;

		InsertChar_f('k', kcode);

		return false;
	};

	function InsertChar_f(mode, c) {
		var TempStr = '';

		if ((c >= 65 && c <= 90) && !IsShift_f) {
			IsCaps = true;
		}
		else if ((c >= 97 && c <= 122) && IsShift_f) {
			IsCaps = true;
		}
		else if ((c >= 65 && c <= 90) || (c >= 97 && c <= 122)) {
			IsCaps = false;
		}
		reset_f();

		if (!IsCaps && !IsShift_f) {
			TempStr = VirtualKey[c];
		}
		else if (IsCaps && !IsShift_f) {
			TempStr = VirtualKeyCaps[c];
		}
		else if (!IsCaps && IsShift_f) {
			TempStr = VirtualKey[c];
		}
		else if (IsCaps && IsShift_f) {
			if (mode == 'k') {
				TempStr = VirtualKeyCaps[c];
			}
			else if (mode == 'm') {
				TempStr = VirtualKey[c];
			}
			else { 
				var newText = c + ' ' + IsCaps + ' ' + IsShift_f + ' ' + mode;

				// document.getElementById('father_name_hindi').value = c + ' ' + IsCaps + ' ' + IsShift_f + ' ' + mode;
				var currentValue = txtHindi.value;

				// Get the current cursor position (selectionStart)
				var selectionStart = txtHindi.selectionStart;

				// Insert the new text at the cursor position
				var newValue = currentValue.substring(0, selectionStart) + newText + currentValue.substring(selectionStart);

				// Update the text area value with the new text
				txtHindi.value = newValue;
				var event = new Event('change', { bubbles: true });
				getCursorPosition_f('father_name_hindi');
				txtHindi.dispatchEvent(event);
			}
		}

		if (TempStr != undefined)

		var txtHindi = document.getElementById('father_name_hindi');

		// Store the current value
		var currentValue = txtHindi.value;

		// Store the current selection start position
		var selectionStart = txtHindi.selectionStart;

		// Append TempStr to the current value
		txtHindi.value = currentValue.substring(0, selectionStart) + TempStr + currentValue.substring(selectionStart);

		// Set the selection start to the end of the appended TempStr
		txtHindi.selectionStart = txtHindi.selectionEnd = selectionStart + TempStr.length;
		 
			document.getElementById('father_name_hindi').focus();
			var event = new Event('change', { bubbles: true });
			txtHindi.dispatchEvent(event);

		//	self.parent.updateText(document.getElementById('father_name_hindi').value);
	}
	function getCursorPosition_f(elementId) {
		var element = document.getElementById(elementId);
		 
		if (element) { 
			return element.selectionStart; // For text areas and input fields
		}
		return -1; // If element not found or not an input field
	}

	function ButtonDown_f(c) {
		if (c == 8) {
			document.getElementById('bSpace_f').style.visibility = "visible";
		}
		else if (c == 9) {
			document.getElementById('tab_f').style.visibility = "visible";
		}
		else if (c == 13) {
			document.getElementById('enter_f').style.visibility = "visible";
		}
		else if (c == 16) {
			IsShift_f = true;
			document.getElementById('Shift_fL_f').style.visibility = "visible";
			document.getElementById('Shift_fR_f').style.visibility = "visible";
		}
		else if (c == 20) {
			IsCaps = !IsCaps;
			
			document.getElementById('capsS_f').style.visibility = "visible";
		}
		else if (c == 32) {
			document.getElementById('Space_f').style.visibility = "visible";
		}
		else if (c == 46) {
			document.getElementById('delete_f').style.visibility = "visible";
		}
		else {
			document.getElementById('Butt_f').style.left = LeftButton[c] + 'px';
			document.getElementById('Butt_f').style.top = (TopButton[c] - 2) + 'px';
			document.getElementById('Butt_f').style.visibility = "visible";

		}
		// alert(1);
		reset_f();
	}

	function ButtonUp_f(c) {
		if (c == 16) {
			IsShift_f = false;
		}

		reset_f();

		document.getElementById('Butt_f').style.visibility = "hidden";
		document.getElementById('Space_f').style.visibility = "hidden";
		document.getElementById('bSpace_f').style.visibility = "hidden";
		document.getElementById('delete_f').style.visibility = "hidden";
		document.getElementById('enter_f').style.visibility = "hidden";
		document.getElementById('tab_f').style.visibility = "hidden";
		if (!IsShift_f) {
			document.getElementById('Shift_fL_f').style.visibility = "hidden";
			document.getElementById('Shift_fR_f').style.visibility = "hidden";
		}
		if (!IsCaps) {
			document.getElementById('capsS_f').style.visibility = "hidden";
			

		}
		

		document.getElementById('father_name_hindi').focus();
	}

	function Shift_f() {
		
		if (document.getElementById('normal_f').style.visibility == "visible") {
			document.getElementById('normal_f').style.visibility = "hidden";
			
			document.getElementById('Shift_c_f').style.visibility = "visible";
		}
		else {
			
			document.getElementById('normal_f').style.visibility = "visible";
			document.getElementById('Shift_c_f').style.visibility = "hidden";
		}
	}

	 
	function customBackspace_f() {
		var txtArea = document.getElementById('father_name_hindi');
		var value = txtArea.value;
		 
		let selectionStart = txtArea.selectionStart;

		// Ensure selectionStart is within bounds
		if (selectionStart > 0) {
			// Remove the character at the selectionStart position
			value = value.slice(0, selectionStart - 1) + value.slice(selectionStart);

			// Update the text area value
			txtArea.value = value;

			// Reset the selection start position to the previous character's position
			txtArea.selectionStart = txtArea.selectionEnd = selectionStart - 1;
		}
	}

</script>
</head>

<body>
	<div class="keyboard" style="position:absolute;position: absolute;left:0%;">
		<span id="Butt_f" style="z-index:1;position:absolute; top:0px; left:0px; visibility:hidden"><img id="shade"
				src="{{ asset("text_translator_images/images/btn.gif") }}" /></span>
		<span id="Space_f" style="z-index:1;position:absolute; top:80px; left:82px; visibility:hidden"><img
				src="{{ asset("text_translator_images/images/space.gif") }}" /></span>
		<span id="bSpace_f" style="z-index:1;position:absolute; top:0px; left:280px; visibility:hidden"><img
				src="{{ asset("text_translator_images/images/bs.gif") }}" /></span>
		<span id="delete_f" style="z-index:1;position:absolute; top:20px; left:270px; visibility:hidden"><img
				src="{{ asset("text_translator_images/images/del.gif") }}" /></span>
		<span id="enter_f"
			style="z-index:1; position:absolute; top:40px; left:256px; visibility:hidden; width: 40px;"><img
				src="{{ asset("text_translator_images/images/enter.gif") }}" /></span>
		<span id="tab_f" style="z-index:1;position:absolute;     top: 19px; left:0px; visibility:hidden">
			<img src="{{ asset("text_translator_images/images/tab.gif") }}" />
		</span>
		<span id="capsS_f" style="z-index:1; position:absolute; top:38px; left:0px; visibility:hidden; width: 40px;"><img
				src="{{ asset("text_translator_images/images/caps.gif") }}" /></span>
		<span id="Shift_fL_f" style="z-index:1; position:absolute; top:58px; left:0px; visibility:hidden; width: 40px;"><img
				src="{{ asset("text_translator_images/images/lShift.gif") }}" /></span>
		<span id="Shift_fR_f"
			style="z-index:1; position:absolute; top:58px; left:247px; visibility:hidden; width: 40px;"><img
				src="{{ asset("text_translator_images/images/rShift.gif") }}" /></span>

		<div style="z-index:0;position:absolute; top:0px; left:0px;">
			<img src="{{ asset("text_translator_images/images/Base_kbd.gif") }}" border="0" usemap="#Map_f" />
			<map name="Map_f" id="Map_f">
				<area shape="rect" coords="280,0,300,19" onclick="customBackspace_f()" />
				<area shape="rect" coords="271,20,300,39" />
				<area shape="rect" coords="257,40,300,59"  />
				<area shape="rect" coords="-6,61,46,80" onmousedown="IsShift_f=!IsShift_f;reset_f();" />
				<area shape="rect" coords="247,61,300,79" onmousedown="IsShift_f=!IsShift_f;reset_f();" />

				<area shape="rect" coords="81,82,196,99" onmousedown="InsertChar_f('m',32)" onmouseup="ButtonUp_f(32)" />
			</map>
		</div>
		<div id="normal_f" style="z-index:1;position:absolute; top:0px; left:0px; visibility:visible">
			<img src="{{ asset("text_translator_images/images/Hindi_normal.gif") }}" border="0" usemap="#Map_f2" />
			<map name="Map_f2" id="Map_f2">
				<area shape="rect" coords="0,0,20,19" onmousedown="ButtonDown_f(96);" onmouseup="ButtonUp_f(96)" />
				<area shape="rect" coords="20,0,40,19" onmousedown="ButtonDown_f(49);InsertChar_f('m',49)"
					onmouseup="ButtonUp_f(49)" />
				<area shape="rect" coords="40,0,60,19" onmousedown="ButtonDown_f(50);InsertChar_f('m',50)"
					onmouseup="ButtonUp_f(50)" />
				<area shape="rect" coords="60,0,80,19" onmousedown="ButtonDown_f(51);InsertChar_f('m',51)"
					onmouseup="ButtonUp_f(51)" />
				<area shape="rect" coords="80,0,100,19" onmousedown="ButtonDown_f(52);InsertChar_f('m',52)"
					onmouseup="ButtonUp_f(52)" />
				<area shape="rect" coords="100,0,120,19" onmousedown="ButtonDown_f(53);InsertChar_f('m',53)"
					onmouseup="ButtonUp_f(53)" />
				<area shape="rect" coords="120,0,140,19" onmousedown="ButtonDown_f(54);InsertChar_f('m',54)"
					onmouseup="ButtonUp_f(54)" />
				<area shape="rect" coords="140,0,160,19" onmousedown="ButtonDown_f(55);InsertChar_f('m',55)"
					onmouseup="ButtonUp_f(55)" />
				<area shape="rect" coords="160,0,180,19" onmousedown="ButtonDown_f(56);InsertChar_f('m',56)"
					onmouseup="ButtonUp_f(56)" />
				<area shape="rect" coords="180,0,200,19" onmousedown="ButtonDown_f(57);InsertChar_f('m',57)"
					onmouseup="ButtonUp_f(57)" />
				<area shape="rect" coords="200,0,220,19" onmousedown="ButtonDown_f(48);InsertChar_f('m',48)"
					onmouseup="ButtonUp_f(48)" />
				<area shape="rect" coords="220,0,240,19" onmousedown="ButtonDown_f(189);InsertChar_f('m',45)"
					onmouseup="ButtonUp_f(189)" />
				<area shape="rect" coords="240,0,260,19" onmousedown="ButtonDown_f(187);InsertChar_f('m',61)"
					onmouseup="ButtonUp_f(187)" />
				<area shape="rect" coords="260,0,280,19" onmousedown="ButtonDown_f(220);InsertChar_f('m',92)"
					onmouseup="ButtonUp_f(220)" />

				<area shape="rect" coords="31,20,51,39" onmousedown="ButtonDown_f(81);InsertChar_f('m',113)"
					onmouseup="ButtonUp_f(81)" />
				<area shape="rect" coords="51,20,71,39" onmousedown="ButtonDown_f(87);InsertChar_f('m',119)"
					onmouseup="ButtonUp_f(87)" />
				<area shape="rect" coords="71,20,91,39" onmousedown="ButtonDown_f(69);InsertChar_f('m',101)"
					onmouseup="ButtonUp_f(69)" />
				<area shape="rect" coords="91,20,111,39" onmousedown="ButtonDown_f(82);InsertChar_f('m',114)"
					onmouseup="ButtonUp_f(82)" />
				<area shape="rect" coords="111,20,131,39" onmousedown="ButtonDown_f(84);InsertChar_f('m',116)"
					onmouseup="ButtonUp_f(84)" />
				<area shape="rect" coords="131,20,151,39" onmousedown="ButtonDown_f(89);InsertChar_f('m',121)"
					onmouseup="ButtonUp_f(89)" />
				<area shape="rect" coords="151,20,171,39" onmousedown="ButtonDown_f(85);InsertChar_f('m',117)"
					onmouseup="ButtonUp_f(85)" />
				<area shape="rect" coords="171,20,191,39" onmousedown="ButtonDown_f(73);InsertChar_f('m',105)"
					onmouseup="ButtonUp_f(73)" />
				<area shape="rect" coords="191,20,211,39" onmousedown="ButtonDown_f(79);InsertChar_f('m',111)"
					onmouseup="ButtonUp_f(79)" />
				<area shape="rect" coords="211,20,231,39" onmousedown="ButtonDown_f(80);InsertChar_f('m',112)"
					onmouseup="ButtonUp_f(80)" />
				<area shape="rect" coords="231,20,251,39" onmousedown="ButtonDown_f(219);InsertChar_f('m',91)"
					onmouseup="ButtonUp_f(219)" />
				<area shape="rect" coords="251,20,271,39" onmousedown="ButtonDown_f(221);InsertChar_f('m',93)"
					onmouseup="ButtonUp_f(221)" />

				<area shape="rect" coords="0,20,30,41" />
				<area shape="rect" coords="271,20,281,39"  />

				<area shape="rect" coords="37,40,57,59" onmousedown="ButtonDown_f(65);InsertChar_f('m',97)"
					onmouseup="ButtonUp_f(65)" />
				<area shape="rect" coords="57,40,77,59" onmousedown="ButtonDown_f(83);InsertChar_f('m',115)"
					onmouseup="ButtonUp_f(83)" />
				<area shape="rect" coords="77,40,97,59" onmousedown="ButtonDown_f(68);InsertChar_f('m',100)"
					onmouseup="ButtonUp_f(68)" />
				<area shape="rect" coords="97,40,117,59" onmousedown="ButtonDown_f(70);InsertChar_f('m',102)"
					onmouseup="ButtonUp_f(70)" />
				<area shape="rect" coords="117,40,137,59" onmousedown="ButtonDown_f(71);InsertChar_f('m',103)"
					onmouseup="ButtonUp_f(71)" />
				<area shape="rect" coords="137,40,157,59" onmousedown="ButtonDown_f(72);InsertChar_f('m',104)"
					onmouseup="ButtonUp_f(72)" />
				<area shape="rect" coords="157,40,177,59" onmousedown="ButtonDown_f(74);InsertChar_f('m',106)"
					onmouseup="ButtonUp_f(74)" />
				<area shape="rect" coords="177,40,197,59" onmousedown="ButtonDown_f(75);InsertChar_f('m',107)"
					onmouseup="ButtonUp_f(75)" />
				<area shape="rect" coords="197,40,217,59" onmousedown="ButtonDown_f(76);InsertChar_f('m',108)"
					onmouseup="ButtonUp_f(76)" />
				<area shape="rect" coords="217,40,237,59" onmousedown="ButtonDown_f(186);InsertChar_f('m',59)"
					onmouseup="ButtonUp_f(186)" />
				<area shape="rect" coords="237,40,257,59" onmousedown="ButtonDown_f(222);InsertChar_f('m',39)"
					onmouseup="ButtonUp_f(222)" />

				<area shape="rect" coords="0,40,38,61" onmousedown="IsCaps=!IsCaps;reset_f();" />
				<area shape="rect" coords="257,40,285,59"  />

				<area shape="rect" coords="47,61,67,80" onmousedown="ButtonDown_f(90);" onmouseup="ButtonUp_f(90)" />
				<area shape="rect" coords="67,61,87,80" onmousedown="ButtonDown_f(88);InsertChar_f('m',120)"
					onmouseup="ButtonUp_f(88)" />
				<area shape="rect" coords="87,61,107,80" onmousedown="ButtonDown_f(67);InsertChar_f('m',99)"
					onmouseup="ButtonUp_f(67)" />
				<area shape="rect" coords="107,61,127,80" onmousedown="ButtonDown_f(86);InsertChar_f('m',118)"
					onmouseup="ButtonUp_f(86)" />
				<area shape="rect" coords="127,61,147,80" onmousedown="ButtonDown_f(66);InsertChar_f('m',98)"
					onmouseup="ButtonUp_f(66)" />
				<area shape="rect" coords="147,61,167,80" onmousedown="ButtonDown_f(78);InsertChar_f('m',110)"
					onmouseup="ButtonUp_f(78)" />
				<area shape="rect" coords="167,61,187,80" onmousedown="ButtonDown_f(77);InsertChar_f('m',109)"
					onmouseup="ButtonUp_f(77)" />
				<area shape="rect" coords="187,61,207,80" onmousedown="ButtonDown_f(188);InsertChar_f('m',44)"
					onmouseup="ButtonUp_f(188)" />
				<area shape="rect" coords="207,61,227,80" onmousedown="ButtonDown_f(190);InsertChar_f('m',46)"
					onmouseup="ButtonUp_f(190)" />
				<area shape="rect" coords="227,61,247,80" onmousedown="ButtonDown_f(191);InsertChar_f('m',47)"
					onmouseup="ButtonUp_f(191)" />

				<area shape="rect" coords="-6,61,46,80" onmousedown="IsShift_f=!IsShift_f;reset_f();" />
				<area shape="rect" coords="247,59,281,83" onmousedown="IsShift_f=!IsShift_f;reset_f();" />

			</map>
		</div>

		<div id="Shift_c_f" style="z-index:1;position:absolute; top:0px; left:0px; visibility:hidden">
			<img src="{{ asset("text_translator_images/images/Hindi_Shift.gif") }}" border="0" usemap="#Map_f3" />
			<map name="Map_f3" id="Map_f3">

				<area shape="rect" coords="0,0,20,19" onmousedown="ButtonDown_f(126);" onmouseup="ButtonUp_f(126)" />
				<area shape="rect" coords="20,0,40,19" onmousedown="ButtonDown_f(33);InsertChar_f('m',33)"
					onmouseup="ButtonUp_f(33)" />
				<area shape="rect" coords="40,0,60,19" onmousedown="ButtonDown_f(64);InsertChar_f('m',64)"
					onmouseup="ButtonUp_f(64)" />
				<area shape="rect" coords="60,0,80,19" onmousedown="ButtonDown_f(35);InsertChar_f('m',35)"
					onmouseup="ButtonUp_f(35)" />
				<area shape="rect" coords="80,0,100,19" onmousedown="ButtonDown_f(36);InsertChar_f('m',36)"
					onmouseup="ButtonUp_f(36)" />
				<area shape="rect" coords="100,0,120,19" onmousedown="ButtonDown_f(37);InsertChar_f('m',37)"
					onmouseup="ButtonUp_f(37)" />
				<area shape="rect" coords="120,0,140,19" onmousedown="ButtonDown_f(94);InsertChar_f('m',94)"
					onmouseup="ButtonUp_f(94)" />
				<area shape="rect" coords="140,0,160,19" onmousedown="ButtonDown_f(38);InsertChar_f('m',38)"
					onmouseup="ButtonUp_f(38)" />
				<area shape="rect" coords="160,0,180,19" onmousedown="ButtonDown_f(42);InsertChar_f('m',42)"
					onmouseup="ButtonUp_f(42)" />
				<area shape="rect" coords="180,0,200,19" onmousedown="ButtonDown_f(40);InsertChar_f('m',40)"
					onmouseup="ButtonUp_f(40)" />
				<area shape="rect" coords="200,0,220,19" onmousedown="ButtonDown_f(41);InsertChar_f('m',41)"
					onmouseup="ButtonUp_f(41)" />
				<area shape="rect" coords="220,0,240,19" onmousedown="ButtonDown_f(95);InsertChar_f('m',95)"
					onmouseup="ButtonUp_f(95)" />
				<area shape="rect" coords="240,0,260,19" onmousedown="ButtonDown_f(43);InsertChar_f('m',43)"
					onmouseup="ButtonUp_f(43)" />
				<area shape="rect" coords="260,0,280,19" onmousedown="ButtonDown_f(124);InsertChar_f('m',124)"
					onmouseup="ButtonUp_f(124)" />

				<area shape="rect" coords="31,20,51,39" onmousedown="ButtonDown_f(81);InsertChar_f('m',81)"
					onmouseup="ButtonUp_f(81)" />
				<area shape="rect" coords="51,20,71,39" onmousedown="ButtonDown_f(87);InsertChar_f('m',87)"
					onmouseup="ButtonUp_f(87)" />
				<area shape="rect" coords="71,20,91,39" onmousedown="ButtonDown_f(69);InsertChar_f('m',69)"
					onmouseup="ButtonUp_f(69)" />
				<area shape="rect" coords="91,20,111,39" onmousedown="ButtonDown_f(82);InsertChar_f('m',82)"
					onmouseup="ButtonUp_f(82)" />
				<area shape="rect" coords="111,20,131,39" onmousedown="ButtonDown_f(84);InsertChar_f('m',84)"
					onmouseup="ButtonUp_f(84)" />
				<area shape="rect" coords="131,20,151,39" onmousedown="ButtonDown_f(89);InsertChar_f('m',89)"
					onmouseup="ButtonUp_f(89)" />
				<area shape="rect" coords="151,20,171,39" onmousedown="ButtonDown_f(85);InsertChar_f('m',85)"
					onmouseup="ButtonUp_f(85)" />
				<area shape="rect" coords="171,20,191,39" onmousedown="ButtonDown_f(73);InsertChar_f('m',73)"
					onmouseup="ButtonUp_f(73)" />
				<area shape="rect" coords="191,20,211,39" onmousedown="ButtonDown_f(79);InsertChar_f('m',79)"
					onmouseup="ButtonUp_f(79)" />
				<area shape="rect" coords="211,20,231,39" onmousedown="ButtonDown_f(80);InsertChar_f('m',80)"
					onmouseup="ButtonUp_f(80)" />
				<area shape="rect" coords="231,20,251,39" onmousedown="ButtonDown_f(219);InsertChar_f('m',123)"
					onmouseup="ButtonUp_f(219)" />
				<area shape="rect" coords="251,20,271,39" onmousedown="ButtonDown_f(221);InsertChar_f('m',125)"
					onmouseup="ButtonUp_f(221)" />

				<area shape="rect" coords="0,20,30,41"  />
				<area shape="rect" coords="271,20,281,39" />

				<area shape="rect" coords="37,40,57,59" onmousedown="ButtonDown_f(65);InsertChar_f('m',65)"
					onmouseup="ButtonUp_f(65)" />
				<area shape="rect" coords="57,40,77,59" onmousedown="ButtonDown_f(83);InsertChar_f('m',83)"
					onmouseup="ButtonUp_f(83)" />
				<area shape="rect" coords="77,40,97,59" onmousedown="ButtonDown_f(68);InsertChar_f('m',68)"
					onmouseup="ButtonUp_f(68)" />
				<area shape="rect" coords="97,40,117,59" onmousedown="ButtonDown_f(70);InsertChar_f('m',70)"
					onmouseup="ButtonUp_f(70)" />
				<area shape="rect" coords="117,40,137,59" onmousedown="ButtonDown_f(71);InsertChar_f('m',71)"
					onmouseup="ButtonUp_f(71)" />
				<area shape="rect" coords="137,40,157,59" onmousedown="ButtonDown_f(72);InsertChar_f('m',72)"
					onmouseup="ButtonUp_f(72)" />
				<area shape="rect" coords="157,40,177,59" onmousedown="ButtonDown_f(74);InsertChar_f('m',74)"
					onmouseup="ButtonUp_f(74)" />
				<area shape="rect" coords="177,40,197,59" onmousedown="ButtonDown_f(75);InsertChar_f('m',75)"
					onmouseup="ButtonUp_f(75)" />
				<area shape="rect" coords="197,40,217,59" onmousedown="ButtonDown_f(76);InsertChar_f('m',76)"
					onmouseup="ButtonUp_f(76)" />
				<area shape="rect" coords="217,40,237,59" onmousedown="ButtonDown_f(186);InsertChar_f('m',58)"
					onmouseup="ButtonUp_f(186)" />
				<area shape="rect" coords="237,40,257,59" onmousedown="ButtonDown_f(222);InsertChar_f('m',34)"
					onmouseup="ButtonUp_f(222)" />

				<area shape="rect" coords="0,40,38,61" onmousedown="IsCaps=!IsCaps;reset_f();" />
				<area shape="rect" coords="257,40,285,59"  />

				<area shape="rect" coords="47,61,67,80" onmousedown="ButtonDown_f(90);" onmouseup="ButtonUp_f(90)" />
				<area shape="rect" coords="67,61,87,80" onmousedown="ButtonDown_f(88);InsertChar_f('m',88)"
					onmouseup="ButtonUp_f(88)" />
				<area shape="rect" coords="87,61,107,80" onmousedown="ButtonDown_f(67);InsertChar_f('m',67)"
					onmouseup="ButtonUp_f(67)" />
				<area shape="rect" coords="107,61,127,80" onmousedown="ButtonDown_f(86);InsertChar_f('m',86)"
					onmouseup="ButtonUp_f(86)" />
				<area shape="rect" coords="127,61,147,80" onmousedown="ButtonDown_f(66);InsertChar_f('m',66)"
					onmouseup="ButtonUp_f(66)" />
				<area shape="rect" coords="147,61,167,80" onmousedown="ButtonDown_f(78);InsertChar_f('m',78)"
					onmouseup="ButtonUp_f(78)" />
				<area shape="rect" coords="167,61,187,80" onmousedown="ButtonDown_f(77);InsertChar_f('m',77)"
					onmouseup="ButtonUp_f(77)" />
				<area shape="rect" coords="187,61,207,80" onmousedown="ButtonDown_f(188);InsertChar_f('m',60)"
					onmouseup="ButtonUp_f(188)" />
				<area shape="rect" coords="207,61,227,80" onmousedown="ButtonDown_f(190);InsertChar_f('m',62)"
					onmouseup="ButtonUp_f(190)" />
				<area shape="rect" coords="227,61,247,80" onmousedown="ButtonDown_f(191);InsertChar_f('m',63)"
					onmouseup="ButtonUp_f(191)" />

				<area shape="rect" coords="-6,61,46,80" onmousedown="IsShift_f=!IsShift_f;reset_f();" />
				<area shape="rect" coords="247,59,281,83" onmousedown="IsShift_f=!IsShift_f;reset_f();" />

			</map>
		</div>

		<div id="caps_f" style="z-index:1;position:absolute; top:0px; left:0px; visibility:hidden">
			<img src="{{ asset("text_translator_images/images/Hindi_Shift.gif") }}" border="0" usemap="#Map_f4" />
			<map name="Map_f4" id="Map_f4">
				<area shape="rect" coords="0,0,20,19" onmousedown="ButtonDown_f(96);" onmouseup="ButtonUp_f(96)" />
				<area shape="rect" coords="20,0,40,19" onmousedown="ButtonDown_f(33);InsertChar_f('m',49)"
					onmouseup="ButtonUp_f(33)" />
				<area shape="rect" coords="40,0,60,19" onmousedown="ButtonDown_f(64);InsertChar_f('m',50)"
					onmouseup="ButtonUp_f(64)" />
				<area shape="rect" coords="60,0,80,19" onmousedown="ButtonDown_f(35);InsertChar_f('m',51)"
					onmouseup="ButtonUp_f(35)" />
				<area shape="rect" coords="80,0,100,19" onmousedown="ButtonDown_f(36);InsertChar_f('m',52)"
					onmouseup="ButtonUp_f(36)" />
				<area shape="rect" coords="100,0,120,19" onmousedown="ButtonDown_f(37);InsertChar_f('m',53)"
					onmouseup="ButtonUp_f(37)" />
				<area shape="rect" coords="120,0,140,19" onmousedown="ButtonDown_f(94);InsertChar_f('m',54)"
					onmouseup="ButtonUp_f(94)" />
				<area shape="rect" coords="140,0,160,19" onmousedown="ButtonDown_f(38);InsertChar_f('m',55)"
					onmouseup="ButtonUp_f(38)" />
				<area shape="rect" coords="160,0,180,19" onmousedown="ButtonDown_f(42);InsertChar_f('m',56)"
					onmouseup="ButtonUp_f(42)" />
				<area shape="rect" coords="180,0,200,19" onmousedown="ButtonDown_f(40);InsertChar_f('m',57)"
					onmouseup="ButtonUp_f(40)" />
				<area shape="rect" coords="200,0,220,19" onmousedown="ButtonDown_f(41);InsertChar_f('m',48)"
					onmouseup="ButtonUp_f(41)" />
				<area shape="rect" coords="220,0,240,19" onmousedown="ButtonDown_f(95);InsertChar_f('m',45)"
					onmouseup="ButtonUp_f(95)" />
				<area shape="rect" coords="240,0,260,19" onmousedown="ButtonDown_f(43);InsertChar_f('m',61)"
					onmouseup="ButtonUp_f(43)" />
				<area shape="rect" coords="260,0,280,19" onmousedown="ButtonDown_f(124);InsertChar_f('m',92)"
					onmouseup="ButtonUp_f(124)" />

				<area shape="rect" coords="31,20,51,39" onmousedown="ButtonDown_f(81);InsertChar_f('m',81)"
					onmouseup="ButtonUp_f(81)" />
				<area shape="rect" coords="51,20,71,39" onmousedown="ButtonDown_f(87);InsertChar_f('m',87)"
					onmouseup="ButtonUp_f(87)" />
				<area shape="rect" coords="71,20,91,39" onmousedown="ButtonDown_f(69);InsertChar_f('m',69)"
					onmouseup="ButtonUp_f(69)" />
				<area shape="rect" coords="91,20,111,39" onmousedown="ButtonDown_f(82);InsertChar_f('m',82)"
					onmouseup="ButtonUp_f(82)" />
				<area shape="rect" coords="111,20,131,39" onmousedown="ButtonDown_f(84);InsertChar_f('m',84)"
					onmouseup="ButtonUp_f(84)" />
				<area shape="rect" coords="131,20,151,39" onmousedown="ButtonDown_f(89);InsertChar_f('m',89)"
					onmouseup="ButtonUp_f(89)" />
				<area shape="rect" coords="151,20,171,39" onmousedown="ButtonDown_f(85);InsertChar_f('m',85)"
					onmouseup="ButtonUp_f(85)" />
				<area shape="rect" coords="171,20,191,39" onmousedown="ButtonDown_f(73);InsertChar_f('m',73)"
					onmouseup="ButtonUp_f(73)" />
				<area shape="rect" coords="191,20,211,39" onmousedown="ButtonDown_f(79);InsertChar_f('m',79)"
					onmouseup="ButtonUp_f(79)" />
				<area shape="rect" coords="211,20,231,39" onmousedown="ButtonDown_f(80);InsertChar_f('m',80)"
					onmouseup="ButtonUp_f(80)" />
				<area shape="rect" coords="231,20,251,39" onmousedown="ButtonDown_f(219);InsertChar_f('m',91)"
					onmouseup="ButtonUp_f(219)" />
				<area shape="rect" coords="251,20,271,39" onmousedown="ButtonDown_f(221);InsertChar_f('m',93)"
					onmouseup="ButtonUp_f(221)" />

				<area shape="rect" coords="0,20,30,41"  />
				<area shape="rect" coords="271,20,281,39"  />

				<area shape="rect" coords="37,40,57,59" onmousedown="ButtonDown_f(65);InsertChar_f('m',65)"
					onmouseup="ButtonUp_f(65)" />
				<area shape="rect" coords="57,40,77,59" onmousedown="ButtonDown_f(83);InsertChar_f('m',83)"
					onmouseup="ButtonUp_f(83)" />
				<area shape="rect" coords="77,40,97,59" onmousedown="ButtonDown_f(68);InsertChar_f('m',68)"
					onmouseup="ButtonUp_f(68)" />
				<area shape="rect" coords="97,40,117,59" onmousedown="ButtonDown_f(70);InsertChar_f('m',70)"
					onmouseup="ButtonUp_f(70)" />
				<area shape="rect" coords="117,40,137,59" onmousedown="ButtonDown_f(71);InsertChar_f('m',71)"
					onmouseup="ButtonUp_f(71)" />
				<area shape="rect" coords="137,40,157,59" onmousedown="ButtonDown_f(72);InsertChar_f('m',72)"
					onmouseup="ButtonUp_f(72)" />
				<area shape="rect" coords="157,40,177,59" onmousedown="ButtonDown_f(74);InsertChar_f('m',74)"
					onmouseup="ButtonUp_f(74)" />
				<area shape="rect" coords="177,40,197,59" onmousedown="ButtonDown_f(75);InsertChar_f('m',75)"
					onmouseup="ButtonUp_f(75)" />
				<area shape="rect" coords="197,40,217,59" onmousedown="ButtonDown_f(76);InsertChar_f('m',76)"
					onmouseup="ButtonUp_f(76)" />
				<area shape="rect" coords="217,40,237,59" onmousedown="ButtonDown_f(186);InsertChar_f('m',59)"
					onmouseup="ButtonUp_f(186)" />
				<area shape="rect" coords="237,40,257,59" onmousedown="ButtonDown_f(222);InsertChar_f('m',39)"
					onmouseup="ButtonUp_f(222)" />

				<area shape="rect" coords="0,40,38,61" onmousedown="IsCaps=!IsCaps;reset_f();" />
				<area shape="rect" coords="257,40,285,59"  />

				<area shape="rect" coords="47,61,67,80" onmousedown="ButtonDown_f(90);" onmouseup="ButtonUp_f(90)" />
				<area shape="rect" coords="67,61,87,80" onmousedown="ButtonDown_f(88);InsertChar_f('m',88)"
					onmouseup="ButtonUp_f(88)" />
				<area shape="rect" coords="87,61,107,80" onmousedown="ButtonDown_f(67);InsertChar_f('m',67)"
					onmouseup="ButtonUp_f(67)" />
				<area shape="rect" coords="107,61,127,80" onmousedown="ButtonDown_f(86);InsertChar_f('m',86)"
					onmouseup="ButtonUp_f(86)" />
				<area shape="rect" coords="127,61,147,80" onmousedown="ButtonDown_f(66);InsertChar_f('m',66)"
					onmouseup="ButtonUp_f(66)" />
				<area shape="rect" coords="147,61,167,80" onmousedown="ButtonDown_f(78);InsertChar_f('m',78)"
					onmouseup="ButtonUp_f(78)" />
				<area shape="rect" coords="167,61,187,80" onmousedown="ButtonDown_f(77);InsertChar_f('m',77)"
					onmouseup="ButtonUp_f(77)" />
				<area shape="rect" coords="187,61,207,80" onmousedown="ButtonDown_f(188);InsertChar_f('m',44)"
					onmouseup="ButtonUp_f(188)" />
				<area shape="rect" coords="207,61,227,80" onmousedown="ButtonDown_f(190);InsertChar_f('m',46)"
					onmouseup="ButtonUp_f(190)" />
				<area shape="rect" coords="227,61,247,80" onmousedown="ButtonDown_f(191);InsertChar_f('m',47)"
					onmouseup="ButtonUp_f(191)" />

				<area shape="rect" coords="-6,61,46,80" onmousedown="IsShift_f=!IsShift_f;reset_f();" />
				<area shape="rect" coords="247,59,281,83" onmousedown="IsShift_f=!IsShift_f;reset_f();" />

			</map>
		</div>

		<div style="z-index:1;position:absolute; top:103px; left:0px;">
			<!-- <input type="text" id="txtHindi"name="txtHindi" style="width:295px; height:30px;"/> -->
		</div>
	</div>
	<script>
		if (navigator.appName != "Mozilla") {
			document.getElementById('father_name_hindi').onkeydown = checkCode_f;
			document.getElementById('father_name_hindi').onkeypress = writeKeyPressed_f;
			document.getElementById('father_name_hindi').onkeyup = restoreCode_f;
		}
		else {
			document.addEventListener("onkeydown", checkCode_f, true);
			document.addEventListener("onkeypress", writeKeyPressed_f, false);
			document.addEventListener("onkeyup", restoreCode_f, true);
		}
	</script>