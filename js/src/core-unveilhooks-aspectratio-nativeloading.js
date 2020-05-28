import './lib/lazysizes';
import install from './install';
import unveilHooks from './lib/ls.unveilhooks';
import aspectRatio from './lib/ls.aspectratio';
import nativeLoading from './lib/ls.native-loading';

install(unveilHooks);
install(aspectRatio);
install(nativeLoading);
