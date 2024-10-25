import pandas as pd

# Load the CSV file
file_path = './sbm.csv'  # Update with your file path if needed
data = pd.read_csv(file_path)

# Generate the formatted output with line breaks for specific terms in 'nilai'
formatted_output = []
for index, row in data.iterrows():
    # Convert each value to a string and replace single quotes
    biaya = str(row['Biaya']).replace("'", "\\'")
    daerah = str(row['Daerah']).replace("'", "\\'")
    satuan = str(row['Satuan']).replace("'", "\\'")
    
    # Insert a semicolon (;) after each specific label in 'nilai' for HTML-friendly line breaks
    nilai = (
        str(row['Nilai'])
        .replace("'", "\\'")
        .replace("Es 1", "Es 1;")
        .replace("Es 2", "Es 2;")
        .replace("Es 3 / Gol IV", "Es 3 / Gol IV;")
        .replace("Gol I - III", "Gol I - III;")
        .replace("Ekonomi", "Ekonomi;")
        .replace("Bisnis", "Bisnis;")
        .replace("Makan Siang", "Makan Siang;")
        .replace("Snack", "Snack;")
        .replace("Fullday / Halfday", "Fullday / Halfday;")
        .replace("Fullboard", "Fullboard;")
    )

    tahun_anggaran = str('2024')
    
    # Format each row as a Laravel seeder array
    formatted_output.append("[\n    'biaya' => '{}',\n    'daerah' => '{}',\n    'satuan' => '{}',\n    'nilai' => '{}',\n    'tahun_anggaran' => '{}',\n],".format(biaya, daerah, satuan, nilai, tahun_anggaran))

# Combine all formatted rows into a single string
formatted_text = "\n".join(formatted_output)

# Write the output to a .txt file
output_path = 'laravel_seeder_output.txt'  # Specify the desired output file name
with open(output_path, 'w') as file:
    file.write(formatted_text)

print(f"Formatted output with semicolon-separated terms has been saved to {output_path}")