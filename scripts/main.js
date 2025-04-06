const fs = require('fs');
const path = require('path');
const fastcsv = require('fast-csv');
const XLSX = require('xlsx');
const iconv = require('iconv-lite');

function clearOutput() {
  

  console.log('Removing files from output folder...');
  fs.readdir(path.join(__dirname, 'output'), (err, files) => {
    if(err) throw err;
    
    files = files.filter(file => file !== '.gitignore'); 
    files.forEach(file => {
      const filePath = path.join(__dirname, 'output', file);
      fs.unlink(filePath, err => {
        if (err) {
          console.error(`Error removing file ${file}:`, err);
        } else {
          console.log(`File ${file} removed successfully.`);
        }
      });
    });
    
  });
}

function convertFileToUTF8(filePath) {
  const content = fs.readFileSync(filePath);
  const utf8Content = iconv.decode(content, 'latin1'); // Assuming the original encoding is latin1
  const utf8Buffer = Buffer.from(utf8Content, 'utf-8');
  fs.writeFileSync(filePath, utf8Buffer);
}

// Function to process all files in a folder
function processFilesInFolder(folderPath) {
  fs.readdir(folderPath, (err, files) => {
    if (err) {
      console.error('Error reading folder:', err);
      return;
    }
    console.log('===========================================')
    files.forEach(file => {
      const filePath = path.join(folderPath, file);
      if(file.startsWith('.gitignore')) return;
      convertFileToUTF8(filePath);
      console.log(`Converted ${file} to UTF-8`);
    });
    console.log('===========================================')

  });
}

async function convertCSVtoXLSX(csvFilePath, xlsxFilePath, delimiter) {
  try {
    // Read CSV file as a stream
    const csvStream = fs.createReadStream(csvFilePath, { encoding: 'utf-8' });

    // Parse CSV content using fast-csv
    const records = await new Promise((resolve, reject) => {
      const data = [];
      fastcsv
        .parseStream(csvStream, { headers: false, delimiter: delimiter })
        .on('data', row => data.push(row))
        .on('end', () => resolve(data))
        .on('error', error => reject(error));
    });

    // Convert parsed data to XLSX
    const ws = XLSX.utils.aoa_to_sheet(records);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet 1');

    // Specify the encoding as 'utf-8' when writing the XLSX file
    XLSX.writeFile(wb, xlsxFilePath, { bookType: 'xlsx', bookSST: false, type: 'binary', encoding: 'utf-8' });

    console.log(`Conversion successful: ${csvFilePath} -> ${xlsxFilePath}`);
  } catch (error) {
    console.error(`Error converting file ${csvFilePath}: ${error.message}`);
  }
}

async function convertCSVFolderToXLSX(folderPath) {
  try {
    console.log('===========================================')
    clearOutput();

    processFilesInFolder(folderPath);
    // let delimiter = prompt('Please type the delimiter, default is ";": ') || ";";
    let delimiter = ';';

    const files = fs.readdirSync(folderPath);

    for (const file of files) {
      const csvFilePath = path.join(folderPath, file);

      if (path.extname(file).toLowerCase() === '.csv') {
        const fileNameWithoutExt = path.basename(file, '.csv');
        const xlsxFilePath = path.join('output', `${fileNameWithoutExt}.xlsx`);
        await convertCSVtoXLSX(csvFilePath, xlsxFilePath, delimiter);
      }
    }
    console.log('===========================================')
    console.log('Success: all csv files changed to xlsx!')
  } catch (error) {
    console.error(`Error reading folder ${folderPath}: ${error.message}`);
  } finally {
    console.log('Removing files from files folder...');
    fs.readdir(path.join(__dirname, 'files'), (err, files) => {
      if(err) throw err;
      
      files = files.filter(file => file !== '.gitignore'); 
      files.forEach(file => {
        const filePath = path.join(__dirname, 'files', file);
        fs.unlink(filePath, err => {
          if (err) {
            console.error(`Error removing file ${file}:`, err);
          } else {
            console.log(`File ${file} removed successfully.`);
          }
        });
      });
      console.log('===========================================')
      
    });
  }
}

const folderPath = 'files';
convertCSVFolderToXLSX(folderPath);
