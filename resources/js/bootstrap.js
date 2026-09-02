import axios from 'axios';
import { installEchoAxiosInterceptor } from './echo';

import './bootstrap-core.js';

installEchoAxiosInterceptor(axios);
