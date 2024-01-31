import './styles/app.css';
import 'htmx.org';

window.htmx = require('htmx.org');

const MainMenu = () => {
  const onLoad = (attributes) => {};
  return { onLoad };
};

const PeriodRangeMenu = () => {
  const id = 'periodRangeButtons';
  const menuElem = window.document.getElementById(id);
  const btns = {
    day: 'todayRangeButton',
    week: 'weekRangeButton',
    month: 'monthRangeButton',
    year: 'yearRangeButton',
  };

  const hide = () => {
    menuElem.style.display = 'none';
  };

  const show = () => {
    menuElem.style.display = 'block';
  };

  const onLoad = ({ page, period }) => {
    ['songs', 'artists'].indexOf(page) < 0 ? hide() : show();

    for (const p in btns) {
      const btn = window.document.getElementById(btns[p]);
      btn.classList.remove('active');
      if (p == period) {
        btn.classList.add('active');
      }

      btn.setAttribute('hx-get', `/charts/${page}/${p}`);
      btn.setAttribute('hx-trigger', 'click');
      btn.setAttribute('hx-target', '#main-pane');
      htmx.process(menuElem);
    }
  };

  return { onLoad };
};

const mainMenu = MainMenu();
const periodRangeMenu = PeriodRangeMenu();

htmx.on('htmx:load', (e) => {
  const attrKeys = Array.from(e.detail.elt.attributes).map((x) => x.nodeName);
  const attributes = {};
  for (const k of attrKeys) {
    const newKeyName = k.replace(/^data\-/, '');
    attributes[newKeyName] = e.detail.elt.attributes.getNamedItem(k).nodeValue;
  }
  mainMenu.onLoad(attributes);
  periodRangeMenu.onLoad(attributes);
});
