<template>
  <div class="mt-2 space-y-2">
    <!-- Strength Bar -->
    <div class="flex items-center gap-2">
      <span class="text-xs font-medium text-neutral-600">Password Strength:</span>
      <div class="flex-1 bg-neutral-200 rounded-full h-2 max-w-32">
        <div 
          class="h-2 rounded-full transition-all duration-300"
          :class="strengthBarClass"
          :style="{ width: strengthPercentage + '%' }"
        ></div>
      </div>
      <span class="text-xs font-medium" :class="strengthTextClass">
        {{ strengthText }}
      </span>
    </div>
    
    <!-- Password Rules -->
    <div class="space-y-1">
      <div class="grid grid-cols-2 gap-1">
        <div 
          v-for="rule in passwordRulesConfig" 
          :key="rule.key"
          class="flex items-center gap-1"
        >
          <Icon 
            :name="passwordRules[rule.key] ? 'lucide:circle-check' : 'lucide:circle-x'"
            :class="passwordRules[rule.key] ? 'text-[#16a34a]' : 'text-[#A7ABB2]'"
            class="w-3 h-3"
          />
          <span class="text-xs" :class="passwordRules[rule.key] ? 'text-[#16a34a]' : 'text-[#8E9198]'">
            {{ rule.message }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Props
const props = defineProps({
  password: {
    type: String,
    default: ''
  }
})

// Password rules configuration
const passwordRulesConfig = [
  {
    key: 'minLength',
    message: '8 characters minimum',
    validator: (password) => password.length >= 8
  },
  {
    key: 'hasLetter',
    message: '1 letter',
    validator: (password) => /[A-Za-z]/.test(password)
  },
  {
    key: 'hasNumber',
    message: '1 number',
    validator: (password) => /[0-9]/.test(password)
  },
  {
    key: 'hasSpecial',
    message: '1 special character',
    validator: (password) => /[@$!%*#?&\-_+=.,:;<>^()[\]{}|~]/.test(password)
  }
]

// Password strength validation
const passwordRules = computed(() => {
  const password = props.password || ''
  const rules = {}
  
  passwordRulesConfig.forEach(rule => {
    rules[rule.key] = rule.validator(password)
  })
  
  return rules
})

// Calculate password strength
const passwordStrength = computed(() => {
  const rules = passwordRules.value
  const passedRules = Object.values(rules).filter(Boolean).length
  return passedRules
})

// Strength percentage for progress bar
const strengthPercentage = computed(() => {
  return (passwordStrength.value / passwordRulesConfig.length) * 100
})

// Strength configuration
const strengthConfig = [
  { threshold: 0, text: 'Very Weak', barClass: 'bg-[#C7C9CE]', textClass: 'text-[#8E9198]' },
  { threshold: 1, text: 'Weak', barClass: 'bg-[#c2351f]', textClass: 'text-[#c2351f]' },
  { threshold: 2, text: 'Fair', barClass: 'bg-[#d97706]', textClass: 'text-[#d97706]' },
  { threshold: 3, text: 'Good', barClass: 'bg-[#0891b2]', textClass: 'text-[#0891b2]' },
  { threshold: passwordRulesConfig.length, text: 'Strong', barClass: 'bg-[#16a34a]', textClass: 'text-[#16a34a]' }
]

// Strength bar styling
const strengthBarClass = computed(() => {
  const strength = passwordStrength.value
  const config = strengthConfig.find(config => strength <= config.threshold) || strengthConfig[strengthConfig.length - 1]
  return config.barClass
})

// Strength text and styling
const strengthText = computed(() => {
  const strength = passwordStrength.value
  const config = strengthConfig.find(config => strength <= config.threshold) || strengthConfig[strengthConfig.length - 1]
  return config.text
})

const strengthTextClass = computed(() => {
  const strength = passwordStrength.value
  const config = strengthConfig.find(config => strength <= config.threshold) || strengthConfig[strengthConfig.length - 1]
  return config.textClass
})
</script>
