import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  base: '/budget-buddy/',   // <-- serve app under /budget-buddy/
  plugins: [react()],
})
