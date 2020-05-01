<script>
  function setupAllMouseovers() {
    console.log('Setting up the mouseovers...');
    let allAreas = document.getElementById('Map').children;
    let areaCount = allAreas.length;
    for(let i = 0; i < areaCount; i++) {
      let area_div = document.createElement('div');
      area_div.setAttribute('id', 'area_' + i);
      let text_span = document.createElement('span');
      text_span.setAttribute('id', 'text_' + i);

      if(allAreas[i].id) {
          text_span.innerHTML = allAreas[i].id + "<br />";
      }
      if(allAreas[i].href && allAreas[i].href.match(/#/)) {
        text_span.innerHTML += "No data yet for this time interval.";
      } else {
        text_span.innerHTML += "CLICK to view data.";
      }
      area_div.appendChild(text_span);
      setTooltip(area_div, text_span, allAreas[i]);

      //allAreas[i].appendChild(area_div);
      document.body.appendChild(area_div);
    }

    function setTooltip(area, text, maparea) {
      console.log('Setting tooltip, the area element is: ', area, ', and the text element is: ', text);
      area.style.position = 'absolute';
      area.style.visibility = 'hidden';
      area.style.width = '115px';

      text.style['text-align'] = 'center';
      text.style.padding = '1px 0';
      text.style['border-top'] = '1px solid gray';
      text.style['border-bottom'] = '1px solid gray';
      text.style['border-left'] = '1px solid gray';
      text.style['border-right'] = '1px solid gray';
      text.style.position = 'absolute';
      text.style['z-index'] = '1';
      text.style.color = '#ff2100';
      text.style['font-family'] = 'Arial, Helvetica, sans-serif';
      text.style['font-size'] = '12px';
      text.style['background-color'] = 'rgba(255,255,255,0.7)';

      // when hovered over
      maparea.onmousemove = function(evt) {
        area.style.top = (evt.pageY+5) + 'px';
        area.style.left = (evt.pageX+5) + 'px';
        area.style.visibility = 'visible';
        console.log('AREA MOUSED OVER!!! area = ', area);
        console.log('X: ', evt.pageX, 'Y: ', evt.pageY);
      }

      // when hovered out
      maparea.onmouseout = function() {
        area.style.visibility = 'hidden';
        console.log('AREA MOUSED OUT!!!');
      }
    }
  }

  window.onload = setupAllMouseovers;
</script>
