const express = require('express');
const path = require('path');
const app = express();

// Serve static files from the Angular app
app.use(express.static(path.join(__dirname, 'dist/tickly/browser')));

// Handle Angular routing - return index.html for all routes
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'dist/tickly/browser/index.html'));
});

const port = process.env.PORT || 4200;
app.listen(port, () => {
  console.log(`Server is running on port ${port}`);
});

