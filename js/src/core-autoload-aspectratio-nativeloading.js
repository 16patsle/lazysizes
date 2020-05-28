import './lib/lazysizes';
import install from './install';
import setAutoLoad from './auto-load';
import aspectRatio from './lib/ls.aspectratio';
import nativeLoading from './lib/ls.native-loading';
import setLoadingAttribute from './loading-attribute';

setAutoLoad();
install(aspectRatio);
install(nativeLoading);
setLoadingAttribute();
