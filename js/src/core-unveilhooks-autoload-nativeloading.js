import './lib/lazysizes';
import install from './install';
import unveilHooks from './lib/ls.unveilhooks';
import setAutoLoad from './auto-load';
import nativeLoading from './lib/ls.native-loading';
import setLoadingAttribute from './loading-attribute';

install(unveilHooks);
setAutoLoad();
install(nativeLoading);
setLoadingAttribute();
