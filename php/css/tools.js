function timer() {
	window.setTimeout(timer, 1000);
	
	var elements = document.getElementsByClassName('js_timer');
	
	for(i = 0; i < elements.length; i++) {
		var tstr = elements[i].innerHTML;
		var split = tstr.split(':');
		var hours = parseInt(split[0]);
		var mins = parseInt(split[1]);
		var secs = parseInt(split[2]);
		
		//count up
		secs += 1;
		if (secs >= 60) { mins += 1; secs -= 60; }
		if (mins >= 60) { hours += 1; mins -= 60; }
		
		//two zeros
		if (secs < 10) {
			secs = "0" + secs;
		}
		if (mins < 10) {
			mins = "0" + mins;
		}
		
		//set new time
		elements[i].innerHTML = hours + ":" + mins + ":" + secs;
	}
}