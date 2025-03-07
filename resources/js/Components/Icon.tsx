import React, { useState, useEffect, useRef } from 'react';

interface Props {
  name: string
  className?: any,
  onClick?: any
  onMouseDown?:any
  key?: any
}

export default function Icon({ name, className="", ...props}: Props){
  
  const ImportedIconRef = useRef(null as any);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    setLoading(true);
    let isActive = true;
    const importIcon = async () => {
      try {
        const {ReactComponent} = await import(`../../assets/icons/${name}.svg`);
        ImportedIconRef.current = ReactComponent;
      } catch (err) {
        throw err;
      } finally {
        if(isActive)
          setLoading(false);
      }
    };
    importIcon();
    return () => { isActive = false };
  }, [name]);

  if (!loading && ImportedIconRef.current) {
    const { current: ImportedIcon } = ImportedIconRef;

    return <ImportedIcon {...props} fill="currentColor" className={className}/>;
  }

  return null;
};