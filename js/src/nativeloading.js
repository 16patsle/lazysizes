import install from './install';
import nativeLoading from './lib/ls.native-loading';

install(nativeLoading);

if (window.lazySizes) {
	window.lazySizes.cfg.nativeLoading.setLoadingAttribute = true;
}
