import './lib/lazysizes';
import install from './install';
import nativeLoading from './lib/ls.native-loading';
import setLoadingAttribute from './loading-attribute';

install(nativeLoading);
setLoadingAttribute();
