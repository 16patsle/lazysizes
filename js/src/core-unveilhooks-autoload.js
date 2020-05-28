import './lib/lazysizes';
import install from './install';
import unveilHooks from './lib/ls.unveilhooks';
import setAutoLoad from './auto-load';

install(unveilHooks);
setAutoLoad();
