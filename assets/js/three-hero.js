/* ==========================================================
   Bharat SEO - Three.js Hero
   Animated connected social network globe.
   Loaded lazily only on capable devices (see main.js).
   Requires THREE (r128) loaded beforehand.
   ========================================================== */
(function () {
  'use strict';
  if (typeof THREE === 'undefined') return;
  var canvas = document.getElementById('hero-three-canvas');
  if (!canvas) return;

  var width = canvas.clientWidth || 460;
  var height = canvas.clientHeight || 420;

  var renderer;
  try {
    renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });
  } catch (e) {
    var fb = document.getElementById('heroFallback');
    if (fb) fb.style.display = 'grid';
    return;
  }
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(width, height, false);

  var scene = new THREE.Scene();
  var camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 100);
  camera.position.z = 5.2;

  var group = new THREE.Group();
  scene.add(group);

  // Globe wireframe
  var globeGeo = new THREE.SphereGeometry(2, 28, 28);
  var globeMat = new THREE.MeshBasicMaterial({ color: 0x4f8cff, wireframe: true, transparent: true, opacity: 0.18 });
  group.add(new THREE.Mesh(globeGeo, globeMat));

  // Nodes on sphere surface
  var nodeCount = 46;
  var nodes = [];
  var nodeGeo = new THREE.SphereGeometry(0.045, 8, 8);
  var palette = [0x6aa1ff, 0x9b5cff, 0x2fe6e0, 0xf5c451];
  for (var i = 0; i < nodeCount; i++) {
    var phi = Math.acos(-1 + (2 * i) / nodeCount);
    var theta = Math.sqrt(nodeCount * Math.PI) * phi;
    var r = 2.05;
    var x = r * Math.cos(theta) * Math.sin(phi);
    var y = r * Math.sin(theta) * Math.sin(phi);
    var z = r * Math.cos(phi);
    var mat = new THREE.MeshBasicMaterial({ color: palette[i % palette.length] });
    var node = new THREE.Mesh(nodeGeo, mat);
    node.position.set(x, y, z);
    group.add(node);
    nodes.push(node);
  }

  // Connections between nearby nodes
  var lineMat = new THREE.LineBasicMaterial({ color: 0x6aa1ff, transparent: true, opacity: 0.22 });
  for (var a = 0; a < nodes.length; a++) {
    for (var b = a + 1; b < nodes.length; b++) {
      if (nodes[a].position.distanceTo(nodes[b].position) < 1.25) {
        var g = new THREE.BufferGeometry().setFromPoints([nodes[a].position, nodes[b].position]);
        group.add(new THREE.Line(g, lineMat));
      }
    }
  }

  // Floating particles
  var pCount = 120;
  var pGeo = new THREE.BufferGeometry();
  var positions = new Float32Array(pCount * 3);
  for (var p = 0; p < pCount; p++) {
    positions[p * 3] = (Math.random() - 0.5) * 9;
    positions[p * 3 + 1] = (Math.random() - 0.5) * 9;
    positions[p * 3 + 2] = (Math.random() - 0.5) * 9;
  }
  pGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
  var pMat = new THREE.PointsMaterial({ color: 0x9b5cff, size: 0.035, transparent: true, opacity: 0.6 });
  scene.add(new THREE.Points(pGeo, pMat));

  // Pointer parallax
  var targetX = 0, targetY = 0;
  canvas.addEventListener('pointermove', function (ev) {
    var rect = canvas.getBoundingClientRect();
    targetX = ((ev.clientX - rect.left) / rect.width - 0.5) * 0.6;
    targetY = ((ev.clientY - rect.top) / rect.height - 0.5) * 0.6;
  });

  var running = true;
  function animate() {
    if (!running) return;
    requestAnimationFrame(animate);
    group.rotation.y += 0.0026;
    group.rotation.x += (targetY - group.rotation.x) * 0.04;
    group.rotation.y += (targetX) * 0.002;
    renderer.render(scene, camera);
  }
  animate();

  // Pause when offscreen / tab hidden to save battery
  document.addEventListener('visibilitychange', function () {
    running = !document.hidden;
    if (running) animate();
  });

  function resize() {
    width = canvas.clientWidth || width;
    height = canvas.clientHeight || height;
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height, false);
  }
  window.addEventListener('resize', resize);
})();
