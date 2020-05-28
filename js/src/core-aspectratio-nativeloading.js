import './lib/lazysizes';
import install from './install';
import aspectRatio from './lib/ls.aspectratio';
import nativeLoading from './lib/ls.native-loading';
import setLoadingAttribute from './loading-attribute';

install(aspectRatio);
install(nativeLoading);
setLoadingAttribute();
