import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from '@/App.vue'
import router from '@/router'
import '@fortawesome/fontawesome-free/css/all.min.css'
import 'sweetalert2/dist/sweetalert2.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import '@/styles/main.css'
import { applyTheme, applyFont, applyIconStyle, getSavedTheme, getSavedFont, getSavedIconStyle } from '@/themes/themes'
import { initResponsiveCards } from '@/utils/responsiveCards'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)

applyTheme(getSavedTheme())
applyFont(getSavedFont())
applyIconStyle(getSavedIconStyle())

app.mount('#app')

initResponsiveCards()
