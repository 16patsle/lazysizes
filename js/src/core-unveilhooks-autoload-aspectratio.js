import './lib/lazysizes';
import install from './install';
import unveilHooks from './lib/ls.unveilhooks';
import setAutoLoad from './auto-load';
import aspectRatio from './lib/ls.aspectratio';

install(unveilHooks);
setAutoLoad();
install(aspectRatio);
