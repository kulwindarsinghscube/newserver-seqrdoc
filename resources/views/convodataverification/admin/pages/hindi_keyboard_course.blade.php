<script language=javascript type="text/javascript">

	var IsValid = false;
	var IsCaps = false;
	var IsShift_c = false;

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

	function checkCode_c(evt) {
		var kcode = 0;

		if (document.all) {
			var evt = window.event;
			kcode = evt.keyCode;
		}
		else kcode = evt.which;

		if (ValidButton[kcode]) { ButtonDown_c(kcode); IsValid = true; } else { IsValid = false; };
	}

	function reset_c() {
		if (!IsCaps && !IsShift_c) {
			document.getElementById('normal_c').style.visibility = "visible";
			document.getElementById('Shift_c_c').style.visibility = "hidden";
			document.getElementById('caps_c').style.visibility = "hidden";
		}
		else if (IsCaps && !IsShift_c) {
			document.getElementById('normal_c').style.visibility = "hidden";
			document.getElementById('Shift_c_c').style.visibility = "hidden";
			document.getElementById('caps_c').style.visibility = "visible";

			document.getElementById('capsS_c').style.visibility = "visible";
		}
		else if (!IsCaps && IsShift_c) {
			document.getElementById('normal_c').style.visibility = "hidden";
			document.getElementById('Shift_c_c').style.visibility = "visible";
			document.getElementById('caps_c').style.visibility = "hidden";
		}
		else if (IsCaps && IsShift_c) {
			
			document.getElementById('normal_c').style.visibility = "visible";
			document.getElementById('Shift_c_c').style.visibility = "hidden";
			document.getElementById('caps_c').style.visibility = "hidden";

			document.getElementById('capsS_c').style.visibility = "visible";
		}
	}

	function restoreCode_c(evt) {
		var kcode = 0;

		if (document.all) {
			var evt = window.event;
			kcode = evt.keyCode;
		}
		else kcode = evt.which;

		ButtonUp_c(kcode);
	}

	function writeKeyPressed_c(evt) {
		var kcode = 0;

		if (document.all) {
			var evt = window.event;
			kcode = evt.keyCode;
		}
		else kcode = evt.which;

		InsertChar_c('k', kcode);

		return false;
	};

	function InsertChar_c(mode, c) {
		var TempStr = '';

		if ((c >= 65 && c <= 90) && !IsShift_c) {
			IsCaps = true;
		}
		else if ((c >= 97 && c <= 122) && IsShift_c) {
			IsCaps = true;
		}
		else if ((c >= 65 && c <= 90) || (c >= 97 && c <= 122)) {
			IsCaps = false;
		}
		reset_c();

		if (!IsCaps && !IsShift_c) {
			TempStr = VirtualKey[c];
		}
		else if (IsCaps && !IsShift_c) {
			TempStr = VirtualKeyCaps[c];
		}
		else if (!IsCaps && IsShift_c) {
			TempStr = VirtualKey[c];
		}
		else if (IsCaps && IsShift_c) {
			if (mode == 'k') {
				TempStr = VirtualKeyCaps[c];
			}
			else if (mode == 'm') {
				TempStr = VirtualKey[c];
			}
			else { 
				var newText = c + ' ' + IsCaps + ' ' + IsShift_c + ' ' + mode;

				// document.getElementById('course_name_hindi').value = c + ' ' + IsCaps + ' ' + IsShift_c + ' ' + mode;
				var currentValue = txtHindi.value;

				// Get the current cursor position (selectionStart)
				var selectionStart = txtHindi.selectionStart;

				// Insert the new text at the cursor position
				var newValue = currentValue.substring(0, selectionStart) + newText + currentValue.substring(selectionStart);

				// Update the text area value with the new text
				txtHindi.value = newValue;
				var event = new Event('change', { bubbles: true });
				getCursorPosition_c('course_name_hindi');
				txtHindi.dispatchEvent(event);
			}
		}

		if (TempStr != undefined)

		var txtHindi = document.getElementById('course_name_hindi');

		// Store the current value
		var currentValue = txtHindi.value;

		// Store the current selection start position
		var selectionStart = txtHindi.selectionStart;

		// Append TempStr to the current value
		txtHindi.value = currentValue.substring(0, selectionStart) + TempStr + currentValue.substring(selectionStart);

		// Set the selection start to the end of the appended TempStr
		txtHindi.selectionStart = txtHindi.selectionEnd = selectionStart + TempStr.length;
		 
			document.getElementById('course_name_hindi').focus();
			var event = new Event('change', { bubbles: true });
			txtHindi.dispatchEvent(event);

		//	self.parent.updateText(document.getElementById('course_name_hindi').value);
	}
	function getCursorPosition_c(elementId) {
		var element = document.getElementById(elementId);
		 
		if (element) { 
			return element.selectionStart; // For text areas and input fields
		}
		return -1; // If element not found or not an input field
	}

	function ButtonDown_c(c) {
		if (c == 8) {
			document.getElementById('bSpace_c').style.visibility = "visible";
		}
		else if (c == 9) {
			document.getElementById('tab_c').style.visibility = "visible";
		}
		else if (c == 13) {
			document.getElementById('enter_c').style.visibility = "visible";
		}
		else if (c == 16) {
			IsShift_c = true;
			document.getElementById('Shift_cL_c').style.visibility = "visible";
			document.getElementById('Shift_cR_c').style.visibility = "visible";
		}
		else if (c == 20) {
			IsCaps = !IsCaps;
			
			document.getElementById('capsS_c').style.visibility = "visible";
		}
		else if (c == 32) {
			document.getElementById('Space_c').style.visibility = "visible";
		}
		else if (c == 46) {
			document.getElementById('delete_c').style.visibility = "visible";
		}
		else {
			document.getElementById('Butt_c').style.left = LeftButton[c] + 'px';
			document.getElementById('Butt_c').style.top = (TopButton[c] - 2) + 'px';
			document.getElementById('Butt_c').style.visibility = "visible";

		}
		// alert(1);
		reset_c();
	}

	function ButtonUp_c(c) {
		if (c == 16) {
			IsShift_c = false;
		}

		reset_c();

		document.getElementById('Butt_c').style.visibility = "hidden";
		document.getElementById('Space_c').style.visibility = "hidden";
		document.getElementById('bSpace_c').style.visibility = "hidden";
		document.getElementById('delete_c').style.visibility = "hidden";
		document.getElementById('enter_c').style.visibility = "hidden";
		document.getElementById('tab_c').style.visibility = "hidden";
		if (!IsShift_c) {
			document.getElementById('Shift_cL_c').style.visibility = "hidden";
			document.getElementById('Shift_cR_c').style.visibility = "hidden";
		}
		if (!IsCaps) {
			document.getElementById('capsS_c').style.visibility = "hidden";
			

		}
		

		document.getElementById('course_name_hindi').focus();
	}

	function Shift_c() {
		
		if (document.getElementById('normal_c').style.visibility == "visible") {
			document.getElementById('normal_c').style.visibility = "hidden";
			
			document.getElementById('Shift_c_c').style.visibility = "visible";
		}
		else {
			
			document.getElementById('normal_c').style.visibility = "visible";
			document.getElementById('Shift_c_c').style.visibility = "hidden";
		}
	}

	 
	function customBackspace_c() {
		var txtArea = document.getElementById('course_name_hindi');
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
		<span id="Butt_c" style="z-index:1;position:absolute; top:0px; left:0px; visibility:hidden"><img id="shade"
				src="{{ asset("text_translator_images/images/btn.gif") }}" /></span>
		<span id="Space_c" style="z-index:1;position:absolute; top:80px; left:82px; visibility:hidden"><img
				src="{{ asset("text_translator_images/images/space.gif") }}" /></span>
		<span id="bSpace_c" style="z-index:1;position:absolute; top:0px; left:280px; visibility:hidden"><img
				src="{{ asset("text_translator_images/images/bs.gif") }}" /></span>
		<span id="delete_c" style="z-index:1;position:absolute; top:20px; left:270px; visibility:hidden"><img
				src="{{ asset("text_translator_images/images/del.gif") }}" /></span>
		<span id="enter_c"
			style="z-index:1; position:absolute; top:40px; left:256px; visibility:hidden; width: 40px;"><img
				src="{{ asset("text_translator_images/images/enter.gif") }}" /></span>
		<span id="tab_c" style="z-index:1;position:absolute;     top: 19px; left:0px; visibility:hidden">
			<img src="{{ asset("text_translator_images/images/tab.gif") }}" />
		</span>
		<span id="capsS_c" style="z-index:1; position:absolute; top:38px; left:0px; visibility:hidden; width: 40px;"><img
				src="{{ asset("text_translator_images/images/caps.gif") }}" /></span>
		<span id="Shift_cL_c" style="z-index:1; position:absolute; top:58px; left:0px; visibility:hidden; width: 40px;"><img
				src="{{ asset("text_translator_images/images/lShift.gif") }}" /></span>
		<span id="Shift_cR_c"
			style="z-index:1; position:absolute; top:58px; left:247px; visibility:hidden; width: 40px;"><img
				src="{{ asset("text_translator_images/images/rShift.gif") }}" /></span>

		<div style="z-index:0;position:absolute; top:0px; left:0px;">
			<img src="{{ asset("text_translator_images/images/Base_kbd.gif") }}" border="0" usemap="#Map_c" />
			<map name="Map_c" id="Map_c">
				<area shape="rect" coords="280,0,300,19" onclick="customBackspace_c()" />
				<area shape="rect" coords="271,20,300,39" />
				<area shape="rect" coords="257,40,300,59"  />
				<area shape="rect" coords="-6,61,46,80" onmousedown="IsShift_c=!IsShift_c;reset_c();" />
				<area shape="rect" coords="247,61,300,79" onmousedown="IsShift_c=!IsShift_c;reset_c();" />

				<area shape="rect" coords="81,82,196,99" onmousedown="InsertChar_c('m',32)" onmouseup="ButtonUp_c(32)" />
			</map>
		</div>
		<div id="normal_c" style="z-index:1;position:absolute; top:0px; left:0px; visibility:visible">
			<img src="{{ asset("text_translator_images/images/Hindi_normal.gif") }}" border="0" usemap="#Map_c2" />
			<map name="Map_c2" id="Map_c2">
				<area shape="rect" coords="0,0,20,19" onmousedown="ButtonDown_c(96);" onmouseup="ButtonUp_c(96)" />
				<area shape="rect" coords="20,0,40,19" onmousedown="ButtonDown_c(49);InsertChar_c('m',49)"
					onmouseup="ButtonUp_c(49)" />
				<area shape="rect" coords="40,0,60,19" onmousedown="ButtonDown_c(50);InsertChar_c('m',50)"
					onmouseup="ButtonUp_c(50)" />
				<area shape="rect" coords="60,0,80,19" onmousedown="ButtonDown_c(51);InsertChar_c('m',51)"
					onmouseup="ButtonUp_c(51)" />
				<area shape="rect" coords="80,0,100,19" onmousedown="ButtonDown_c(52);InsertChar_c('m',52)"
					onmouseup="ButtonUp_c(52)" />
				<area shape="rect" coords="100,0,120,19" onmousedown="ButtonDown_c(53);InsertChar_c('m',53)"
					onmouseup="ButtonUp_c(53)" />
				<area shape="rect" coords="120,0,140,19" onmousedown="ButtonDown_c(54);InsertChar_c('m',54)"
					onmouseup="ButtonUp_c(54)" />
				<area shape="rect" coords="140,0,160,19" onmousedown="ButtonDown_c(55);InsertChar_c('m',55)"
					onmouseup="ButtonUp_c(55)" />
				<area shape="rect" coords="160,0,180,19" onmousedown="ButtonDown_c(56);InsertChar_c('m',56)"
					onmouseup="ButtonUp_c(56)" />
				<area shape="rect" coords="180,0,200,19" onmousedown="ButtonDown_c(57);InsertChar_c('m',57)"
					onmouseup="ButtonUp_c(57)" />
				<area shape="rect" coords="200,0,220,19" onmousedown="ButtonDown_c(48);InsertChar_c('m',48)"
					onmouseup="ButtonUp_c(48)" />
				<area shape="rect" coords="220,0,240,19" onmousedown="ButtonDown_c(189);InsertChar_c('m',45)"
					onmouseup="ButtonUp_c(189)" />
				<area shape="rect" coords="240,0,260,19" onmousedown="ButtonDown_c(187);InsertChar_c('m',61)"
					onmouseup="ButtonUp_c(187)" />
				<area shape="rect" coords="260,0,280,19" onmousedown="ButtonDown_c(220);InsertChar_c('m',92)"
					onmouseup="ButtonUp_c(220)" />

				<area shape="rect" coords="31,20,51,39" onmousedown="ButtonDown_c(81);InsertChar_c('m',113)"
					onmouseup="ButtonUp_c(81)" />
				<area shape="rect" coords="51,20,71,39" onmousedown="ButtonDown_c(87);InsertChar_c('m',119)"
					onmouseup="ButtonUp_c(87)" />
				<area shape="rect" coords="71,20,91,39" onmousedown="ButtonDown_c(69);InsertChar_c('m',101)"
					onmouseup="ButtonUp_c(69)" />
				<area shape="rect" coords="91,20,111,39" onmousedown="ButtonDown_c(82);InsertChar_c('m',114)"
					onmouseup="ButtonUp_c(82)" />
				<area shape="rect" coords="111,20,131,39" onmousedown="ButtonDown_c(84);InsertChar_c('m',116)"
					onmouseup="ButtonUp_c(84)" />
				<area shape="rect" coords="131,20,151,39" onmousedown="ButtonDown_c(89);InsertChar_c('m',121)"
					onmouseup="ButtonUp_c(89)" />
				<area shape="rect" coords="151,20,171,39" onmousedown="ButtonDown_c(85);InsertChar_c('m',117)"
					onmouseup="ButtonUp_c(85)" />
				<area shape="rect" coords="171,20,191,39" onmousedown="ButtonDown_c(73);InsertChar_c('m',105)"
					onmouseup="ButtonUp_c(73)" />
				<area shape="rect" coords="191,20,211,39" onmousedown="ButtonDown_c(79);InsertChar_c('m',111)"
					onmouseup="ButtonUp_c(79)" />
				<area shape="rect" coords="211,20,231,39" onmousedown="ButtonDown_c(80);InsertChar_c('m',112)"
					onmouseup="ButtonUp_c(80)" />
				<area shape="rect" coords="231,20,251,39" onmousedown="ButtonDown_c(219);InsertChar_c('m',91)"
					onmouseup="ButtonUp_c(219)" />
				<area shape="rect" coords="251,20,271,39" onmousedown="ButtonDown_c(221);InsertChar_c('m',93)"
					onmouseup="ButtonUp_c(221)" />

				<area shape="rect" coords="0,20,30,41" />
				<area shape="rect" coords="271,20,281,39"  />

				<area shape="rect" coords="37,40,57,59" onmousedown="ButtonDown_c(65);InsertChar_c('m',97)"
					onmouseup="ButtonUp_c(65)" />
				<area shape="rect" coords="57,40,77,59" onmousedown="ButtonDown_c(83);InsertChar_c('m',115)"
					onmouseup="ButtonUp_c(83)" />
				<area shape="rect" coords="77,40,97,59" onmousedown="ButtonDown_c(68);InsertChar_c('m',100)"
					onmouseup="ButtonUp_c(68)" />
				<area shape="rect" coords="97,40,117,59" onmousedown="ButtonDown_c(70);InsertChar_c('m',102)"
					onmouseup="ButtonUp_c(70)" />
				<area shape="rect" coords="117,40,137,59" onmousedown="ButtonDown_c(71);InsertChar_c('m',103)"
					onmouseup="ButtonUp_c(71)" />
				<area shape="rect" coords="137,40,157,59" onmousedown="ButtonDown_c(72);InsertChar_c('m',104)"
					onmouseup="ButtonUp_c(72)" />
				<area shape="rect" coords="157,40,177,59" onmousedown="ButtonDown_c(74);InsertChar_c('m',106)"
					onmouseup="ButtonUp_c(74)" />
				<area shape="rect" coords="177,40,197,59" onmousedown="ButtonDown_c(75);InsertChar_c('m',107)"
					onmouseup="ButtonUp_c(75)" />
				<area shape="rect" coords="197,40,217,59" onmousedown="ButtonDown_c(76);InsertChar_c('m',108)"
					onmouseup="ButtonUp_c(76)" />
				<area shape="rect" coords="217,40,237,59" onmousedown="ButtonDown_c(186);InsertChar_c('m',59)"
					onmouseup="ButtonUp_c(186)" />
				<area shape="rect" coords="237,40,257,59" onmousedown="ButtonDown_c(222);InsertChar_c('m',39)"
					onmouseup="ButtonUp_c(222)" />

				<area shape="rect" coords="0,40,38,61" onmousedown="IsCaps=!IsCaps;reset_c();" />
				<area shape="rect" coords="257,40,285,59"  />

				<area shape="rect" coords="47,61,67,80" onmousedown="ButtonDown_c(90);" onmouseup="ButtonUp_c(90)" />
				<area shape="rect" coords="67,61,87,80" onmousedown="ButtonDown_c(88);InsertChar_c('m',120)"
					onmouseup="ButtonUp_c(88)" />
				<area shape="rect" coords="87,61,107,80" onmousedown="ButtonDown_c(67);InsertChar_c('m',99)"
					onmouseup="ButtonUp_c(67)" />
				<area shape="rect" coords="107,61,127,80" onmousedown="ButtonDown_c(86);InsertChar_c('m',118)"
					onmouseup="ButtonUp_c(86)" />
				<area shape="rect" coords="127,61,147,80" onmousedown="ButtonDown_c(66);InsertChar_c('m',98)"
					onmouseup="ButtonUp_c(66)" />
				<area shape="rect" coords="147,61,167,80" onmousedown="ButtonDown_c(78);InsertChar_c('m',110)"
					onmouseup="ButtonUp_c(78)" />
				<area shape="rect" coords="167,61,187,80" onmousedown="ButtonDown_c(77);InsertChar_c('m',109)"
					onmouseup="ButtonUp_c(77)" />
				<area shape="rect" coords="187,61,207,80" onmousedown="ButtonDown_c(188);InsertChar_c('m',44)"
					onmouseup="ButtonUp_c(188)" />
				<area shape="rect" coords="207,61,227,80" onmousedown="ButtonDown_c(190);InsertChar_c('m',46)"
					onmouseup="ButtonUp_c(190)" />
				<area shape="rect" coords="227,61,247,80" onmousedown="ButtonDown_c(191);InsertChar_c('m',47)"
					onmouseup="ButtonUp_c(191)" />

				<area shape="rect" coords="-6,61,46,80" onmousedown="IsShift_c=!IsShift_c;reset_c();" />
				<area shape="rect" coords="247,59,281,83" onmousedown="IsShift_c=!IsShift_c;reset_c();" />

			</map>
		</div>

		<div id="Shift_c_c" style="z-index:1;position:absolute; top:0px; left:0px; visibility:hidden">
			<img src="{{ asset("text_translator_images/images/Hindi_Shift.gif") }}" border="0" usemap="#Map_c3" />
			<map name="Map_c3" id="Map_c3">

				<area shape="rect" coords="0,0,20,19" onmousedown="ButtonDown_c(126);" onmouseup="ButtonUp_c(126)" />
				<area shape="rect" coords="20,0,40,19" onmousedown="ButtonDown_c(33);InsertChar_c('m',33)"
					onmouseup="ButtonUp_c(33)" />
				<area shape="rect" coords="40,0,60,19" onmousedown="ButtonDown_c(64);InsertChar_c('m',64)"
					onmouseup="ButtonUp_c(64)" />
				<area shape="rect" coords="60,0,80,19" onmousedown="ButtonDown_c(35);InsertChar_c('m',35)"
					onmouseup="ButtonUp_c(35)" />
				<area shape="rect" coords="80,0,100,19" onmousedown="ButtonDown_c(36);InsertChar_c('m',36)"
					onmouseup="ButtonUp_c(36)" />
				<area shape="rect" coords="100,0,120,19" onmousedown="ButtonDown_c(37);InsertChar_c('m',37)"
					onmouseup="ButtonUp_c(37)" />
				<area shape="rect" coords="120,0,140,19" onmousedown="ButtonDown_c(94);InsertChar_c('m',94)"
					onmouseup="ButtonUp_c(94)" />
				<area shape="rect" coords="140,0,160,19" onmousedown="ButtonDown_c(38);InsertChar_c('m',38)"
					onmouseup="ButtonUp_c(38)" />
				<area shape="rect" coords="160,0,180,19" onmousedown="ButtonDown_c(42);InsertChar_c('m',42)"
					onmouseup="ButtonUp_c(42)" />
				<area shape="rect" coords="180,0,200,19" onmousedown="ButtonDown_c(40);InsertChar_c('m',40)"
					onmouseup="ButtonUp_c(40)" />
				<area shape="rect" coords="200,0,220,19" onmousedown="ButtonDown_c(41);InsertChar_c('m',41)"
					onmouseup="ButtonUp_c(41)" />
				<area shape="rect" coords="220,0,240,19" onmousedown="ButtonDown_c(95);InsertChar_c('m',95)"
					onmouseup="ButtonUp_c(95)" />
				<area shape="rect" coords="240,0,260,19" onmousedown="ButtonDown_c(43);InsertChar_c('m',43)"
					onmouseup="ButtonUp_c(43)" />
				<area shape="rect" coords="260,0,280,19" onmousedown="ButtonDown_c(124);InsertChar_c('m',124)"
					onmouseup="ButtonUp_c(124)" />

				<area shape="rect" coords="31,20,51,39" onmousedown="ButtonDown_c(81);InsertChar_c('m',81)"
					onmouseup="ButtonUp_c(81)" />
				<area shape="rect" coords="51,20,71,39" onmousedown="ButtonDown_c(87);InsertChar_c('m',87)"
					onmouseup="ButtonUp_c(87)" />
				<area shape="rect" coords="71,20,91,39" onmousedown="ButtonDown_c(69);InsertChar_c('m',69)"
					onmouseup="ButtonUp_c(69)" />
				<area shape="rect" coords="91,20,111,39" onmousedown="ButtonDown_c(82);InsertChar_c('m',82)"
					onmouseup="ButtonUp_c(82)" />
				<area shape="rect" coords="111,20,131,39" onmousedown="ButtonDown_c(84);InsertChar_c('m',84)"
					onmouseup="ButtonUp_c(84)" />
				<area shape="rect" coords="131,20,151,39" onmousedown="ButtonDown_c(89);InsertChar_c('m',89)"
					onmouseup="ButtonUp_c(89)" />
				<area shape="rect" coords="151,20,171,39" onmousedown="ButtonDown_c(85);InsertChar_c('m',85)"
					onmouseup="ButtonUp_c(85)" />
				<area shape="rect" coords="171,20,191,39" onmousedown="ButtonDown_c(73);InsertChar_c('m',73)"
					onmouseup="ButtonUp_c(73)" />
				<area shape="rect" coords="191,20,211,39" onmousedown="ButtonDown_c(79);InsertChar_c('m',79)"
					onmouseup="ButtonUp_c(79)" />
				<area shape="rect" coords="211,20,231,39" onmousedown="ButtonDown_c(80);InsertChar_c('m',80)"
					onmouseup="ButtonUp_c(80)" />
				<area shape="rect" coords="231,20,251,39" onmousedown="ButtonDown_c(219);InsertChar_c('m',123)"
					onmouseup="ButtonUp_c(219)" />
				<area shape="rect" coords="251,20,271,39" onmousedown="ButtonDown_c(221);InsertChar_c('m',125)"
					onmouseup="ButtonUp_c(221)" />

				<area shape="rect" coords="0,20,30,41"  />
				<area shape="rect" coords="271,20,281,39" />

				<area shape="rect" coords="37,40,57,59" onmousedown="ButtonDown_c(65);InsertChar_c('m',65)"
					onmouseup="ButtonUp_c(65)" />
				<area shape="rect" coords="57,40,77,59" onmousedown="ButtonDown_c(83);InsertChar_c('m',83)"
					onmouseup="ButtonUp_c(83)" />
				<area shape="rect" coords="77,40,97,59" onmousedown="ButtonDown_c(68);InsertChar_c('m',68)"
					onmouseup="ButtonUp_c(68)" />
				<area shape="rect" coords="97,40,117,59" onmousedown="ButtonDown_c(70);InsertChar_c('m',70)"
					onmouseup="ButtonUp_c(70)" />
				<area shape="rect" coords="117,40,137,59" onmousedown="ButtonDown_c(71);InsertChar_c('m',71)"
					onmouseup="ButtonUp_c(71)" />
				<area shape="rect" coords="137,40,157,59" onmousedown="ButtonDown_c(72);InsertChar_c('m',72)"
					onmouseup="ButtonUp_c(72)" />
				<area shape="rect" coords="157,40,177,59" onmousedown="ButtonDown_c(74);InsertChar_c('m',74)"
					onmouseup="ButtonUp_c(74)" />
				<area shape="rect" coords="177,40,197,59" onmousedown="ButtonDown_c(75);InsertChar_c('m',75)"
					onmouseup="ButtonUp_c(75)" />
				<area shape="rect" coords="197,40,217,59" onmousedown="ButtonDown_c(76);InsertChar_c('m',76)"
					onmouseup="ButtonUp_c(76)" />
				<area shape="rect" coords="217,40,237,59" onmousedown="ButtonDown_c(186);InsertChar_c('m',58)"
					onmouseup="ButtonUp_c(186)" />
				<area shape="rect" coords="237,40,257,59" onmousedown="ButtonDown_c(222);InsertChar_c('m',34)"
					onmouseup="ButtonUp_c(222)" />

				<area shape="rect" coords="0,40,38,61" onmousedown="IsCaps=!IsCaps;reset_c();" />
				<area shape="rect" coords="257,40,285,59"  />

				<area shape="rect" coords="47,61,67,80" onmousedown="ButtonDown_c(90);" onmouseup="ButtonUp_c(90)" />
				<area shape="rect" coords="67,61,87,80" onmousedown="ButtonDown_c(88);InsertChar_c('m',88)"
					onmouseup="ButtonUp_c(88)" />
				<area shape="rect" coords="87,61,107,80" onmousedown="ButtonDown_c(67);InsertChar_c('m',67)"
					onmouseup="ButtonUp_c(67)" />
				<area shape="rect" coords="107,61,127,80" onmousedown="ButtonDown_c(86);InsertChar_c('m',86)"
					onmouseup="ButtonUp_c(86)" />
				<area shape="rect" coords="127,61,147,80" onmousedown="ButtonDown_c(66);InsertChar_c('m',66)"
					onmouseup="ButtonUp_c(66)" />
				<area shape="rect" coords="147,61,167,80" onmousedown="ButtonDown_c(78);InsertChar_c('m',78)"
					onmouseup="ButtonUp_c(78)" />
				<area shape="rect" coords="167,61,187,80" onmousedown="ButtonDown_c(77);InsertChar_c('m',77)"
					onmouseup="ButtonUp_c(77)" />
				<area shape="rect" coords="187,61,207,80" onmousedown="ButtonDown_c(188);InsertChar_c('m',60)"
					onmouseup="ButtonUp_c(188)" />
				<area shape="rect" coords="207,61,227,80" onmousedown="ButtonDown_c(190);InsertChar_c('m',62)"
					onmouseup="ButtonUp_c(190)" />
				<area shape="rect" coords="227,61,247,80" onmousedown="ButtonDown_c(191);InsertChar_c('m',63)"
					onmouseup="ButtonUp_c(191)" />

				<area shape="rect" coords="-6,61,46,80" onmousedown="IsShift_c=!IsShift_c;reset_c();" />
				<area shape="rect" coords="247,59,281,83" onmousedown="IsShift_c=!IsShift_c;reset_c();" />

			</map>
		</div>

		<div id="caps_c" style="z-index:1;position:absolute; top:0px; left:0px; visibility:hidden">
			<img src="{{ asset("text_translator_images/images/Hindi_Shift.gif") }}" border="0" usemap="#Map_c4" />
			<map name="Map_c4" id="Map_c4">
				<area shape="rect" coords="0,0,20,19" onmousedown="ButtonDown_c(96);" onmouseup="ButtonUp_c(96)" />
				<area shape="rect" coords="20,0,40,19" onmousedown="ButtonDown_c(33);InsertChar_c('m',49)"
					onmouseup="ButtonUp_c(33)" />
				<area shape="rect" coords="40,0,60,19" onmousedown="ButtonDown_c(64);InsertChar_c('m',50)"
					onmouseup="ButtonUp_c(64)" />
				<area shape="rect" coords="60,0,80,19" onmousedown="ButtonDown_c(35);InsertChar_c('m',51)"
					onmouseup="ButtonUp_c(35)" />
				<area shape="rect" coords="80,0,100,19" onmousedown="ButtonDown_c(36);InsertChar_c('m',52)"
					onmouseup="ButtonUp_c(36)" />
				<area shape="rect" coords="100,0,120,19" onmousedown="ButtonDown_c(37);InsertChar_c('m',53)"
					onmouseup="ButtonUp_c(37)" />
				<area shape="rect" coords="120,0,140,19" onmousedown="ButtonDown_c(94);InsertChar_c('m',54)"
					onmouseup="ButtonUp_c(94)" />
				<area shape="rect" coords="140,0,160,19" onmousedown="ButtonDown_c(38);InsertChar_c('m',55)"
					onmouseup="ButtonUp_c(38)" />
				<area shape="rect" coords="160,0,180,19" onmousedown="ButtonDown_c(42);InsertChar_c('m',56)"
					onmouseup="ButtonUp_c(42)" />
				<area shape="rect" coords="180,0,200,19" onmousedown="ButtonDown_c(40);InsertChar_c('m',57)"
					onmouseup="ButtonUp_c(40)" />
				<area shape="rect" coords="200,0,220,19" onmousedown="ButtonDown_c(41);InsertChar_c('m',48)"
					onmouseup="ButtonUp_c(41)" />
				<area shape="rect" coords="220,0,240,19" onmousedown="ButtonDown_c(95);InsertChar_c('m',45)"
					onmouseup="ButtonUp_c(95)" />
				<area shape="rect" coords="240,0,260,19" onmousedown="ButtonDown_c(43);InsertChar_c('m',61)"
					onmouseup="ButtonUp_c(43)" />
				<area shape="rect" coords="260,0,280,19" onmousedown="ButtonDown_c(124);InsertChar_c('m',92)"
					onmouseup="ButtonUp_c(124)" />

				<area shape="rect" coords="31,20,51,39" onmousedown="ButtonDown_c(81);InsertChar_c('m',81)"
					onmouseup="ButtonUp_c(81)" />
				<area shape="rect" coords="51,20,71,39" onmousedown="ButtonDown_c(87);InsertChar_c('m',87)"
					onmouseup="ButtonUp_c(87)" />
				<area shape="rect" coords="71,20,91,39" onmousedown="ButtonDown_c(69);InsertChar_c('m',69)"
					onmouseup="ButtonUp_c(69)" />
				<area shape="rect" coords="91,20,111,39" onmousedown="ButtonDown_c(82);InsertChar_c('m',82)"
					onmouseup="ButtonUp_c(82)" />
				<area shape="rect" coords="111,20,131,39" onmousedown="ButtonDown_c(84);InsertChar_c('m',84)"
					onmouseup="ButtonUp_c(84)" />
				<area shape="rect" coords="131,20,151,39" onmousedown="ButtonDown_c(89);InsertChar_c('m',89)"
					onmouseup="ButtonUp_c(89)" />
				<area shape="rect" coords="151,20,171,39" onmousedown="ButtonDown_c(85);InsertChar_c('m',85)"
					onmouseup="ButtonUp_c(85)" />
				<area shape="rect" coords="171,20,191,39" onmousedown="ButtonDown_c(73);InsertChar_c('m',73)"
					onmouseup="ButtonUp_c(73)" />
				<area shape="rect" coords="191,20,211,39" onmousedown="ButtonDown_c(79);InsertChar_c('m',79)"
					onmouseup="ButtonUp_c(79)" />
				<area shape="rect" coords="211,20,231,39" onmousedown="ButtonDown_c(80);InsertChar_c('m',80)"
					onmouseup="ButtonUp_c(80)" />
				<area shape="rect" coords="231,20,251,39" onmousedown="ButtonDown_c(219);InsertChar_c('m',91)"
					onmouseup="ButtonUp_c(219)" />
				<area shape="rect" coords="251,20,271,39" onmousedown="ButtonDown_c(221);InsertChar_c('m',93)"
					onmouseup="ButtonUp_c(221)" />

				<area shape="rect" coords="0,20,30,41"  />
				<area shape="rect" coords="271,20,281,39"  />

				<area shape="rect" coords="37,40,57,59" onmousedown="ButtonDown_c(65);InsertChar_c('m',65)"
					onmouseup="ButtonUp_c(65)" />
				<area shape="rect" coords="57,40,77,59" onmousedown="ButtonDown_c(83);InsertChar_c('m',83)"
					onmouseup="ButtonUp_c(83)" />
				<area shape="rect" coords="77,40,97,59" onmousedown="ButtonDown_c(68);InsertChar_c('m',68)"
					onmouseup="ButtonUp_c(68)" />
				<area shape="rect" coords="97,40,117,59" onmousedown="ButtonDown_c(70);InsertChar_c('m',70)"
					onmouseup="ButtonUp_c(70)" />
				<area shape="rect" coords="117,40,137,59" onmousedown="ButtonDown_c(71);InsertChar_c('m',71)"
					onmouseup="ButtonUp_c(71)" />
				<area shape="rect" coords="137,40,157,59" onmousedown="ButtonDown_c(72);InsertChar_c('m',72)"
					onmouseup="ButtonUp_c(72)" />
				<area shape="rect" coords="157,40,177,59" onmousedown="ButtonDown_c(74);InsertChar_c('m',74)"
					onmouseup="ButtonUp_c(74)" />
				<area shape="rect" coords="177,40,197,59" onmousedown="ButtonDown_c(75);InsertChar_c('m',75)"
					onmouseup="ButtonUp_c(75)" />
				<area shape="rect" coords="197,40,217,59" onmousedown="ButtonDown_c(76);InsertChar_c('m',76)"
					onmouseup="ButtonUp_c(76)" />
				<area shape="rect" coords="217,40,237,59" onmousedown="ButtonDown_c(186);InsertChar_c('m',59)"
					onmouseup="ButtonUp_c(186)" />
				<area shape="rect" coords="237,40,257,59" onmousedown="ButtonDown_c(222);InsertChar_c('m',39)"
					onmouseup="ButtonUp_c(222)" />

				<area shape="rect" coords="0,40,38,61" onmousedown="IsCaps=!IsCaps;reset_c();" />
				<area shape="rect" coords="257,40,285,59"  />

				<area shape="rect" coords="47,61,67,80" onmousedown="ButtonDown_c(90);" onmouseup="ButtonUp_c(90)" />
				<area shape="rect" coords="67,61,87,80" onmousedown="ButtonDown_c(88);InsertChar_c('m',88)"
					onmouseup="ButtonUp_c(88)" />
				<area shape="rect" coords="87,61,107,80" onmousedown="ButtonDown_c(67);InsertChar_c('m',67)"
					onmouseup="ButtonUp_c(67)" />
				<area shape="rect" coords="107,61,127,80" onmousedown="ButtonDown_c(86);InsertChar_c('m',86)"
					onmouseup="ButtonUp_c(86)" />
				<area shape="rect" coords="127,61,147,80" onmousedown="ButtonDown_c(66);InsertChar_c('m',66)"
					onmouseup="ButtonUp_c(66)" />
				<area shape="rect" coords="147,61,167,80" onmousedown="ButtonDown_c(78);InsertChar_c('m',78)"
					onmouseup="ButtonUp_c(78)" />
				<area shape="rect" coords="167,61,187,80" onmousedown="ButtonDown_c(77);InsertChar_c('m',77)"
					onmouseup="ButtonUp_c(77)" />
				<area shape="rect" coords="187,61,207,80" onmousedown="ButtonDown_c(188);InsertChar_c('m',44)"
					onmouseup="ButtonUp_c(188)" />
				<area shape="rect" coords="207,61,227,80" onmousedown="ButtonDown_c(190);InsertChar_c('m',46)"
					onmouseup="ButtonUp_c(190)" />
				<area shape="rect" coords="227,61,247,80" onmousedown="ButtonDown_c(191);InsertChar_c('m',47)"
					onmouseup="ButtonUp_c(191)" />

				<area shape="rect" coords="-6,61,46,80" onmousedown="IsShift_c=!IsShift_c;reset_c();" />
				<area shape="rect" coords="247,59,281,83" onmousedown="IsShift_c=!IsShift_c;reset_c();" />

			</map>
		</div>

		<div style="z-index:1;position:absolute; top:103px; left:0px;">
			<!-- <input type="text" id="txtHindi"name="txtHindi" style="width:295px; height:30px;"/> -->
		</div>
	</div>
	<script>
		if (navigator.appName != "Mozilla") {
			document.getElementById('course_name_hindi').onkeydown = checkCode_c;
			document.getElementById('course_name_hindi').onkeypress = writeKeyPressed_c;
			document.getElementById('course_name_hindi').onkeyup = restoreCode_c;
		}
		else {
			document.addEventListener("onkeydown", checkCode_c, true);
			document.addEventListener("onkeypress", writeKeyPressed_c, false);
			document.addEventListener("onkeyup", restoreCode_c, true);
		}
	</script>