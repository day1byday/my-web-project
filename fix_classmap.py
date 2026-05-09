"""Regenerate classmap to include all app/ and vendor/ classes"""
import os
import re

vendor_dir = 'D:/my-web-project/vendor'
app_dir = 'D:/my-web-project/app'
output_file = 'D:/my-web-project/vendor/composer/autoload_classmap.php'

classmap = {}

class_patterns = [
    re.compile(r'(?:^|\s)(?:abstract\s+)?class\s+(\w+)\s*(?:extends|implements|{)', re.MULTILINE),
    re.compile(r'(?:^|\s)interface\s+(\w+)\s*(?:extends|{)', re.MULTILINE),
    re.compile(r'(?:^|\s)trait\s+(\w+)\s*(?:{)', re.MULTILINE),
]

def scan_directory(base_dir, prefix=''):
    for root, dirs, files in os.walk(base_dir):
        segs = root.split(os.sep)
        if 'composer' in segs:
            continue
        
        for f in files:
            if not f.endswith('.php'):
                continue
            
            path = os.path.join(root, f)
            
            if prefix:
                rel_path = os.path.relpath(path, prefix).replace('\\', '/')
            else:
                rel_path = path.replace('\\', '/')
            
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as fh:
                    content = fh.read(8000)
            except:
                continue
            
            ns_match = re.search(r'namespace\s+([^;{]+)', content)
            ns = ns_match.group(1).strip() if ns_match else ''
            
            for pattern in class_patterns:
                for match in pattern.finditer(content):
                    cls_name = match.group(1)
                    if ns.startswith('db'):
                        pass  # db 命名空间允许小写类名（如 db_user）
                    elif len(cls_name) < 2 or cls_name[0].islower():
                        continue
                    fqcn = (ns + '\\' + cls_name) if ns else cls_name
                    classmap[fqcn] = rel_path

print("Scanning vendor...")
scan_directory(vendor_dir, vendor_dir)

print("Scanning app...")
for root, dirs, files in os.walk(app_dir):
    for f in files:
        if not f.endswith('.php'):
            continue
        
        path = os.path.join(root, f)
        rel_path = '../' + os.path.relpath(path, os.path.dirname(vendor_dir)).replace('\\', '/')
        
        try:
            with open(path, 'r', encoding='utf-8', errors='ignore') as fh:
                content = fh.read(8000)
        except:
            continue
        
        ns_match = re.search(r'namespace\s+([^;{]+)', content)
        ns = ns_match.group(1).strip() if ns_match else ''
        
        for pattern in class_patterns:
            for match in pattern.finditer(content):
                cls_name = match.group(1)
                if len(cls_name) < 2 or cls_name[0].islower():
                    continue
                fqcn = (ns + '\\' + cls_name) if ns else cls_name
                classmap[fqcn] = rel_path

print("Scanning db...")
db_dir = os.path.join(os.path.dirname(vendor_dir), 'db')
if os.path.exists(db_dir):
    for root, dirs, files in os.walk(db_dir):
        for f in files:
            if not f.endswith('.php'):
                continue
            
            path = os.path.join(root, f)
            rel_path = '../' + os.path.relpath(path, os.path.dirname(vendor_dir)).replace('\\', '/')
            
            try:
                with open(path, 'r', encoding='utf-8', errors='ignore') as fh:
                    content = fh.read(8000)
            except:
                continue
            
            ns_match = re.search(r'namespace\s+([^;{]+)', content)
            ns = ns_match.group(1).strip() if ns_match else ''
            
            for pattern in class_patterns:
                for match in pattern.finditer(content):
                    cls_name = match.group(1)
                    if ns.startswith('db'):
                        pass  # db 命名空间允许小写类名（如 db_user）
                    elif len(cls_name) < 2 or cls_name[0].islower():
                        continue
                    fqcn = (ns + '\\' + cls_name) if ns else cls_name
                    classmap[fqcn] = rel_path

for k in list(classmap.keys()):
    if classmap[k] == 'topthink/think-orm/stubs/Facade.php':
        classmap[k] = 'topthink/framework/src/think/Facade.php'

print(f"Writing {len(classmap)} classes...")
with open(output_file, 'w', encoding='utf-8') as f:
    f.write('<?php\n\n// Autoload classmap (auto-generated)\nreturn [\n')
    for cls, path in sorted(classmap.items()):
        cls_e = cls.replace("'", "\\'")
        path_e = path.replace("'", "\\'")
        f.write(f"    '{cls_e}' => '{path_e}',\n")
    f.write('];\n')

app_classes = [k for k in classmap if k.startswith('app\\')]
print(f'App classes found: {app_classes}')
