const xmlContent = `...`; // 你的 XML 内容
const container = document.createElement('div');
document.body.appendChild(container);

mxClient.create(document, container, (graph) => {
  const decoder = new mxCodec(xmlContent);
  const model = new mxGraphModel();
  decoder.decode(model.getRoot(), document.querySelector('mxGraphModel'));
  graph.setModel(model);
  // 导出为 Data URL
  const imgData = graph.getImageData(null, 1, '#FFFFFF');
  const img = document.createElement('img');
  img.src = imgData;
  document.body.appendChild(img);
});