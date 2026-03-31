export function downloadCsv(
  headers: string[],
  data: (string | number)[][],
  filename = "data.csv"
) {
  const rows = [headers, ...data];
  const csvContent = rows
    .map(row => row.map(item => `"${item}"`).join(","))
    .join("\n");

  const blob = new Blob([csvContent], { type: "text/csv" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(a.href);
}


export function toTitleCase(string: string): string {
  if (!string) return ""; // Handle empty strings
  return string
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}
