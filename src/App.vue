<template>
  <div class="reservation-container">
    <h2>Rezerwacja Sali</h2>

    <form v-if="!isSubmitted" @submit.prevent="submitReservation">
      <div class="form-group">
        <label>Imię i nazwisko:</label>
        <input v-model="formData.name" type="text" required />
      </div>

      <div class="form-group">
        <label>Data przyjęcia:</label>
        <input v-model="formData.date" type="date" required />
      </div>

      <div class="honeypot-field" aria-hidden="true">
        <label for="website">Strona WWW:</label>
        <input id="website" v-model="formData.website" type="text" tabindex="-1" autocomplete="off" />
      </div>

      <div class="form-group">
        <label>Liczba gości:</label>
        <input v-model="formData.guests" type="number" required />
      </div>
        
      <button type="submit" class="submit-btn" :disabled="isSending">
        {{ isSending ? 'Wysyłanie...' : 'Wyślij zapytanie' }}
      </button>

      <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>
    </form>

    <div v-else class="success-message">
      <h3>Dziękujemy!</h3>
      <p>Zapytanie o termin zostało wysłane pomyślnie.</p>
      <button @click="resetForm" class="reset-btn">Wyślij kolejne</button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const formData = reactive({ 
  name: '', 
  date: '', 
  guests: '',
  website: '' // <-- Pole dla bota
})

const isSubmitted = ref(false)
const isSending = ref(false)
const errorMessage = ref('')

const submitReservation = async () => {
  isSending.value = true
  errorMessage.value = ''

  try {
    const response = await fetch('wyslij.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    })

    const textResponse = await response.text()
    
    try {
      const result = JSON.parse(textResponse)
      
      if (response.ok && result.status === 'success') {
        isSubmitted.value = true 
      } else {
        errorMessage.value = result.message || 'Wystąpił błąd po stronie serwera.'
      }
    } catch (parseError) {
      console.error("Serwer zwrócił nieprawidłowe dane:", textResponse)
      errorMessage.value = 'Błąd serwera. Sprawdź konsolę po więcej szczegółów.'
    }

  } catch (error) {
    console.error('Błąd połączenia:', error)
    errorMessage.value = 'Brak połączenia z serwerem.'
  } finally {
    isSending.value = false
  }
}

const resetForm = () => {
  formData.name = ''
  formData.date = ''
  formData.guests = ''
  formData.website = '' 
  isSubmitted.value = false
  errorMessage.value = ''
}
</script>

<style scoped>
.reservation-container { max-width: 400px; margin: 40px auto; padding: 20px; font-family: sans-serif; border: 1px solid #eee; border-radius: 8px; }
.form-group { margin-bottom: 15px; display: flex; flex-direction: column; }
input { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
button { padding: 12px; background: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px; }
button:disabled { background: #999; cursor: not-allowed; }
.reset-btn { background: #008CBA; margin-top: 15px; }
.error-msg { color: red; margin-top: 15px; font-weight: bold; }
.success-message { text-align: center; color: #2c3e50; }
.honeypot-field {
  opacity: 0;
  position: absolute;
  top: 0;
  left: 0;
  height: 0;
  width: 0;
  z-index: -1;
  overflow: hidden;
}
</style>
