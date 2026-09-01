---
paths:
  - 'resources/**'
---

# Resources

## Reserve WhatsApp trip numbers on the server
Every prefilled WhatsApp booking or service message must reserve its trip number through the server endpoint before opening WhatsApp. Never generate or accept the trip number from browser input; append the server response as `رقم الرحلة: {number}`.
